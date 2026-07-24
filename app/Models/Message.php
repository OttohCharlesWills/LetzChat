<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Message extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'type',
        'body',
        'attachment_path',
        'attachment_name',
        'attachment_size',
        'duration_seconds',
        'reply_to_id',
        'edited_at',
    ];

    protected $casts = [
        'edited_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function (Message $message) {
            $message->uuid = (string) Str::uuid();
        });

        // Keep the conversation's "last message" pointer up to date so the
        // chat list can sort/preview without an expensive join every time.
        static::created(function (Message $message) {
            $message->conversation()->update([
                'last_message_id' => $message->id,
                'last_message_at' => $message->created_at,
            ]);
        });
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function replyTo()
    {
        return $this->belongsTo(Message::class, 'reply_to_id');
    }

    /**
     * Shared JSON shape used both when fetching message history (ChatController)
     * and when broadcasting a new message (MessageSent event) — keeps the
     * frontend's rendering logic identical for both cases.
     */
    public function toChatArray(): array
{
    return [
        'id'         => $this->id,
        'uuid'       => $this->uuid,
        'body'       => $this->trashed() ? null : $this->body,
        'is_deleted' => $this->trashed(),
        'type'       => $this->type,

        'attachment_url'  => $this->attachment_path,
        'attachment_name' => $this->attachment_name,
        'attachment_size' => $this->attachment_size,
        'duration_seconds'=> $this->duration_seconds,

        'created_at' => $this->created_at->toIso8601String(),

        'sender_id'  => $this->sender_id,

        'sender' => [
            'id'         => $this->sender->id,
            'first_name' => $this->sender->first_name,
            'last_name'  => $this->sender->last_name,
            'avatar'     => $this->sender->avatar
                ? asset('storage/' . $this->sender->avatar)
                : null,
        ],
    ];
}
}