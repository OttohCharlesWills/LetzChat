<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Group;
use App\Models\Message;
use App\Models\Sticker;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Chat list — every active conversation for the current user,
     * newest first. This is the "Chats" list (full page version of
     * the Messenger flyout). Includes both private DMs and group chats.
     */
    public function index()
    {
        $userId = Auth::id();

        $conversations = Conversation::forUser($userId)
            ->with(['participants', 'lastMessage.sender', 'group'])
            ->orderByDesc('last_message_at')
            ->get();

        return view('chat.index', compact('conversations'));
    }

    /**
     * Find the existing 1:1 conversation with $user, or create one,
     * then redirect into it. This is what the Messenger flyout /
     * "Message" buttons should point to.
     */
    public function startOrOpen(Request $request, User $user)
    {
        $currentUserId = Auth::id();

        if ($user->id === $currentUserId) {
            abort(403);
        }

        $conversation = Conversation::where('type', 'private')
            ->whereHas('participants', fn ($q) => $q->where('user_id', $currentUserId))
            ->whereHas('participants', fn ($q) => $q->where('user_id', $user->id))
            ->first();

        if (!$conversation) {
            $conversation = DB::transaction(function () use ($currentUserId, $user) {
                $conversation = Conversation::create([
                    'type'       => 'private',
                    'created_by' => $currentUserId,
                ]);

                $conversation->participants()->attach([
                    $currentUserId => ['role' => 'member'],
                    $user->id      => ['role' => 'member'],
                ]);

                return $conversation;
            });
        }

        if ($request->wantsJson()) {
            $messages = $conversation->messages()
                ->with('sender')
                ->orderBy('created_at')
                ->get();

            $conversation->participants()->updateExistingPivot($currentUserId, [
                'last_read_at' => now(),
            ]);

            return response()->json([
                'conversation' => [
                    'id'   => $conversation->id,
                    'uuid' => $conversation->uuid,
                ],
                'messages' => $messages->map->toChatArray()->values(),
            ]);
        }

        return redirect()->route('chat.show', $conversation->uuid);
    }

    /**
     * Open (or lazily create) a group's chat conversation, then behave
     * exactly like `show()` — this is what the "Chat" tab inside a
     * group should point to.
     */
    public function openGroupChat(Request $request, Group $group)
    {
        $userId = Auth::id();
        $user = Auth::user();

        abort_unless($group->isMember($user), 403);

        $conversation = $group->getOrCreateConversation();

        $this->authorizeParticipant($conversation, $userId);

        $messages = $conversation->messages()
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        $conversation->participants()->updateExistingPivot($userId, [
            'last_read_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'conversation' => [
                    'id'   => $conversation->id,
                    'uuid' => $conversation->uuid,
                    'name' => $conversation->name,
                ],
                'messages' => $messages->map->toChatArray()->values(),
            ]);
        }

        return view('chat.show', [
            'conversation' => $conversation,
            'messages'     => $messages,
            'otherUser'    => null,
            'group'        => $group,
        ]);
    }

    /**
     * View a single conversation's message thread, and mark it read.
     * Works for both private and group conversations.
     */
    public function show(Conversation $conversation)
    {
        $userId = Auth::id();
        $this->authorizeParticipant($conversation, $userId);

        $messages = $conversation->messages()
            ->with('sender')
            ->orderBy('created_at')
            ->get();

        $conversation->participants()->updateExistingPivot($userId, [
            'last_read_at' => now(),
        ]);

        $otherUser = $conversation->otherParticipant($userId);

        return view('chat.show', compact('conversation', 'messages', 'otherUser'));
    }

    /**
     * Send a message into a conversation. Broadcasts to the other participant(s)
     * in real time via Reverb, and returns JSON for the sender's own UI to append.
     */
    public function sendMessage(Request $request, Conversation $conversation)
    {
        $userId = Auth::id();
        $this->authorizeParticipant($conversation, $userId);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $message = $conversation->messages()->create([
            'sender_id' => $userId,
            'type'      => 'text',
            'body'      => $validated['body'],
        ]);

        $message->load('sender');

        $conversation->participants()->updateExistingPivot($userId, [
            'last_read_at' => now(),
        ]);

        broadcast(new MessageSent($message))->toOthers();

        if ($request->wantsJson()) {
            return response()->json($message->toChatArray());
        }

        return back();
    }

    /**
     * Upload and send an image into a conversation. Works for both private
     * and group chats since it's keyed off Conversation, same as text/voice.
     */
    public function sendImage(Request $request, Conversation $conversation)
    {
        $userId = Auth::id();
        $this->authorizeParticipant($conversation, $userId);

        $validated = $request->validate([
            'image'   => ['required', 'file', 'image', 'max:8192'],
            'caption' => ['nullable', 'string', 'max:1000'],
        ]);

        $uploaded = $this->uploadToSupabase($validated['image'], 'image');

        $message = $conversation->messages()->create([
            'sender_id'       => $userId,
            'type'            => 'image',
            'body'            => $validated['caption'] ?? null,
            'attachment_path' => $uploaded['url'],
            'attachment_name' => $uploaded['name'],
            'attachment_size' => $uploaded['size'],
        ]);

        $message->load('sender');

        $conversation->participants()->updateExistingPivot($userId, [
            'last_read_at' => now(),
        ]);

        broadcast(new MessageSent($message))->toOthers();

        if ($request->wantsJson()) {
            return response()->json($message->toChatArray());
        }

        return back();
    }

    /**
     * Send a sticker (from the managed Sticker library) into a conversation.
     * No upload involved — just references the sticker's existing image.
     */
    public function sendSticker(Request $request, Conversation $conversation)
    {
        $userId = Auth::id();
        $this->authorizeParticipant($conversation, $userId);

        $validated = $request->validate([
            'sticker_uuid' => ['required', 'uuid', 'exists:stickers,uuid'],
        ]);

        $sticker = Sticker::active()->where('uuid', $validated['sticker_uuid'])->firstOrFail();

        $message = $conversation->messages()->create([
            'sender_id'       => $userId,
            'type'            => 'sticker',
            'attachment_path' => $sticker->image_path,
            'attachment_name' => $sticker->name,
        ]);

        $message->load('sender');

        $conversation->participants()->updateExistingPivot($userId, [
            'last_read_at' => now(),
        ]);

        broadcast(new MessageSent($message))->toOthers();

        if ($request->wantsJson()) {
            return response()->json($message->toChatArray());
        }

        return back();
    }

    /**
     * Save a recorded voice note (uploaded as multipart audio blob) and
     * broadcast it exactly like a text message.
     */
    public function sendVoiceNote(Request $request, Conversation $conversation)
    {
        $userId = Auth::id();
        $this->authorizeParticipant($conversation, $userId);

        $validated = $request->validate([
            'audio'    => ['required', 'file', 'mimes:webm,ogg,mp3,mp4,wav,m4a', 'max:10240'],
            'duration' => ['nullable', 'integer', 'min:0', 'max:600'],
        ]);

        $uploaded = $this->uploadToSupabase($validated['audio'], 'voice');

        $message = $conversation->messages()->create([
            'sender_id'        => $userId,
            'type'             => 'voice',
            'attachment_path'  => $uploaded['url'],
            'attachment_name'  => $uploaded['name'],
            'attachment_size'  => $uploaded['size'],
            'duration_seconds' => $validated['duration'] ?? null,
        ]);

        $message->load('sender');

        $conversation->participants()->updateExistingPivot($userId, [
            'last_read_at' => now(),
        ]);

        broadcast(new MessageSent($message))->toOthers();

        if ($request->wantsJson()) {
            return response()->json($message->toChatArray());
        }

        return back();
    }

    /**
     * Upload a file to Supabase storage and return its public URL + metadata.
     * Shared by sendImage() and sendVoiceNote() so the upload logic only
     * lives in one place.
     */
    private function uploadToSupabase(\Illuminate\Http\UploadedFile $file, string $prefix): array
    {
        $fileName = $prefix . '_' . Str::uuid() . '.' . $file->getClientOriginalExtension();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.supabase.secret'),
            'apikey'        => config('services.supabase.secret'),
            'x-upsert'      => 'false',
            'Content-Type'  => $file->getMimeType(),
        ])
        ->withBody(
            file_get_contents($file->getRealPath()),
            $file->getMimeType()
        )
        ->post(
            config('services.supabase.url')
            . '/storage/v1/object/'
            . config('services.supabase.bucket')
            . '/'
            . $fileName
        );

        if (! $response->successful()) {
            throw new \Exception($response->body());
        }

        $publicUrl = config('services.supabase.url')
            . '/storage/v1/object/public/'
            . config('services.supabase.bucket')
            . '/'
            . $fileName;

        return [
            'url'  => $publicUrl,
            'name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
        ];
    }

    private function authorizeParticipant(Conversation $conversation, int $userId): void
    {
        $isParticipant = $conversation->participants()
            ->where('user_id', $userId)
            ->wherePivotNull('left_at')
            ->exists();

        abort_unless($isParticipant, 403);
    }
}