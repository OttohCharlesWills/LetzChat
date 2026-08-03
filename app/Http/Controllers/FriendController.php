<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FriendController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * "Home" tab: a preview of pending requests + a preview of suggestions.
     * This is what facebook.com/friends shows by default.
     */
    public function index()
    {
        $userId = Auth::id();

        $incomingRequests = Friendship::pending()
            ->where('addressee_id', $userId)
            ->with('requester')
            ->latest()
            ->take(8)
            ->get();

        $totalIncoming = Friendship::pending()->where('addressee_id', $userId)->count();

        $this->attachMutualCounts($incomingRequests->pluck('requester'), $userId);

        $suggestions = $this->buildSuggestions($userId, 8);

        return view('friends.index', [
            'incomingRequests' => $incomingRequests,
            'totalIncoming'    => $totalIncoming,
            'suggestions'      => $suggestions,
        ]);
    }

    /**
     * Full, paginated list of every pending incoming request.
     */
    public function requestsPage()
    {
        $userId = Auth::id();

        $incomingRequests = Friendship::pending()
            ->where('addressee_id', $userId)
            ->with('requester')
            ->latest()
            ->paginate(12);

        $this->attachMutualCounts($incomingRequests->getCollection()->pluck('requester'), $userId);

        return view('friends.requests', compact('incomingRequests'));
    }

    /**
     * Full list of "People You May Know", simple offset-based pagination
     * since ranking is computed in PHP rather than a plain DB paginate.
     */
    public function suggestionsPage(Request $request)
    {
        $userId = Auth::id();
        $page   = max((int) $request->query('page', 1), 1);
        $perPage = 16;

        $ranked = $this->buildSuggestions($userId, $page * $perPage);

        $suggestions = $ranked->slice(($page - 1) * $perPage, $perPage)->values();
        $hasMore     = $ranked->count() > $page * $perPage;

        return view('friends.suggestions', compact('suggestions', 'page', 'hasMore'));
    }

    /**
     * Your actual accepted friends list.
     */
    public function allFriends()
    {
        $userId = Auth::id();

        $friendships = Friendship::accepted()
            ->involvingUser($userId)
            ->with(['requester', 'addressee'])
            ->get();

        $friends = $friendships->map(function (Friendship $f) use ($userId) {
            $friend = $f->requester_id === $userId ? $f->addressee : $f->requester;
            $friend->friendship_id = $f->id;
            return $friend;
        });

        return view('friends.all', compact('friends'));
    }

    /**
     * Send a friend request to another user.
     */
    public function sendRequest(Request $request, User $user)
    {
        $currentUserId = Auth::id();

        if ($user->id === $currentUserId) {
            return back()->withErrors(['error' => "You can't send a friend request to yourself."]);
        }

        $existing = Friendship::where(function ($q) use ($currentUserId, $user) {
                $q->where('requester_id', $currentUserId)->where('addressee_id', $user->id);
            })
            ->orWhere(function ($q) use ($currentUserId, $user) {
                $q->where('requester_id', $user->id)->where('addressee_id', $currentUserId);
            })
            ->first();

        if ($existing) {
            if ($existing->status === 'accepted') {
                return back()->withErrors(['error' => 'You are already friends with this user.']);
            }

            if ($existing->status === 'pending') {
                return back()->withErrors(['error' => 'A friend request is already pending with this user.']);
            }

            if ($existing->status === 'blocked') {
                return back()->withErrors(['error' => 'You cannot send a friend request to this user.']);
            }

            // Any other leftover state (e.g. an old 'declined' row that
            // never got cleaned up) shouldn't block a fresh request.
            $existing->delete();
        }

        $friendship = Friendship::create([
            'requester_id' => $currentUserId,
            'addressee_id' => $user->id,
            'status'       => 'pending',
        ]);

        $user->notify(new \App\Notifications\FriendRequestReceived($friendship));

        UserActivity::create([
            'user_id'    => $currentUserId,
            'device_id'  => null,
            'event_name' => 'friend_request_sent',
            'context'    => 'friendship',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata'   => [
                'friendship_id'  => $friendship->id,
                'target_user_id' => $user->id,
            ],
        ]);

        return back()->with('status', 'Friend request sent.');
    }

    public function accept(Request $request, Friendship $friendship)
    {
        abort_unless($friendship->addressee_id === Auth::id(), 403);

        $friendship->update([
            'status'      => 'accepted',
            'accepted_at' => now(),
        ]);

        $friendship->requester->notify(new \App\Notifications\FriendRequestAccepted($friendship));

        UserActivity::create([
            'user_id'    => Auth::id(),
            'device_id'  => null,
            'event_name' => 'friend_request_accepted',
            'context'    => 'friendship',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata'   => [
                'friendship_id'  => $friendship->id,
                'target_user_id' => $friendship->requester_id,
            ],
        ]);

        return back()->with('status', 'Friend request accepted.');
    }

    public function decline(Request $request, Friendship $friendship)
    {
        abort_unless($friendship->addressee_id === Auth::id(), 403);

        UserActivity::create([
            'user_id'    => Auth::id(),
            'device_id'  => null,
            'event_name' => 'friend_request_declined',
            'context'    => 'friendship',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata'   => [
                'friendship_id'  => $friendship->id,
                'target_user_id' => $friendship->requester_id,
            ],
        ]);

        // Delete rather than mark 'declined' — a decline shouldn't
        // permanently block either user from requesting again later.
        $friendship->delete();

        return back()->with('status', 'Friend request declined.');
    }

    /**
     * Cancel a sent request OR unfriend an existing friend — same table, same action.
     */
    public function destroy(Request $request, Friendship $friendship)
    {
        $userId = Auth::id();

        abort_unless(
            $friendship->requester_id === $userId || $friendship->addressee_id === $userId,
            403
        );

        $otherUserId = $friendship->requester_id === $userId
            ? $friendship->addressee_id
            : $friendship->requester_id;

        $wasAccepted = $friendship->status === 'accepted';

        $friendship->delete();

        UserActivity::create([
            'user_id'    => $userId,
            'device_id'  => null,
            'event_name' => $wasAccepted ? 'friend_removed' : 'friend_request_cancelled',
            'context'    => 'friendship',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'metadata'   => [
                'target_user_id' => $otherUserId,
            ],
        ]);

        return back()->with('status', $wasAccepted ? 'Friend removed.' : 'Request cancelled.');
    }

    // ==========================================================
    // Internal helpers
    // ==========================================================

    /**
     * All accepted-friend user ids for a given user.
     */
    private function friendIds(int $userId): array
    {
        return Friendship::accepted()
            ->involvingUser($userId)
            ->get()
            ->map(fn (Friendship $f) => $f->requester_id === $userId ? $f->addressee_id : $f->requester_id)
            ->values()
            ->all();
    }

    /**
     * All user ids involved in a pending friendship with the given user (either direction).
     */
    private function pendingIds(int $userId): array
    {
        return Friendship::pending()
            ->involvingUser($userId)
            ->get()
            ->map(fn (Friendship $f) => $f->requester_id === $userId ? $f->addressee_id : $f->requester_id)
            ->values()
            ->all();
    }

    /**
     * Attaches a ->mutual_count property to each user in the given collection,
     * representing how many friends they share with the current user.
     */
    private function attachMutualCounts($users, int $currentUserId): void
    {
        $users = $users->filter(); // drop any nulls
        if ($users->isEmpty()) {
            return;
        }

        $myFriendIds  = $this->friendIds($currentUserId);
        $candidateIds = $users->pluck('id')->unique()->values()->all();
        $counts       = $this->mutualCountsFor($candidateIds, $myFriendIds);

        foreach ($users as $user) {
            $user->mutual_count = $counts[$user->id] ?? 0;
        }
    }

    /**
     * Core mutual-friend-count computation: for each candidate id, how many of
     * $myFriendIds are also friends with that candidate.
     */
    private function mutualCountsFor(array $candidateIds, array $myFriendIds): array
    {
        $counts = array_fill_keys($candidateIds, 0);

        if (empty($candidateIds) || empty($myFriendIds)) {
            return $counts;
        }

        $rows = DB::table('friendships')
            ->where('status', 'accepted')
            ->where(function ($q) use ($candidateIds) {
                $q->whereIn('requester_id', $candidateIds)
                  ->orWhereIn('addressee_id', $candidateIds);
            })
            ->get(['requester_id', 'addressee_id']);

        $myFriendIdsFlipped = array_flip($myFriendIds);

        foreach ($rows as $row) {
            if (in_array($row->requester_id, $candidateIds, true)) {
                $candidate = $row->requester_id;
                $other     = $row->addressee_id;
            } else {
                $candidate = $row->addressee_id;
                $other     = $row->requester_id;
            }

            if (isset($myFriendIdsFlipped[$other])) {
                $counts[$candidate] = ($counts[$candidate] ?? 0) + 1;
            }
        }

        return $counts;
    }

    /**
     * Builds a "People You May Know" list ranked by mutual friend count.
     * Strategy: friends-of-friends first (most relevant), padded out with
     * other active users if there aren't enough candidates yet.
     */
    private function buildSuggestions(int $userId, int $limit)
    {
        $myFriendIds = $this->friendIds($userId);
        $excludeIds  = array_merge($myFriendIds, $this->pendingIds($userId), [$userId]);

        $candidateIds = [];

        if (!empty($myFriendIds)) {
            $foFRows = DB::table('friendships')
                ->where('status', 'accepted')
                ->where(function ($q) use ($myFriendIds) {
                    $q->whereIn('requester_id', $myFriendIds)
                      ->orWhereIn('addressee_id', $myFriendIds);
                })
                ->get(['requester_id', 'addressee_id']);

            foreach ($foFRows as $row) {
                foreach ([$row->requester_id, $row->addressee_id] as $id) {
                    if (!in_array($id, $excludeIds, true)) {
                        $candidateIds[] = $id;
                    }
                }
            }
            $candidateIds = array_values(array_unique($candidateIds));
        }

        // Pad with other users if friends-of-friends isn't enough (e.g. new account)
        if (count($candidateIds) < $limit) {
            $needed = $limit - count($candidateIds);
            $fallbackIds = User::whereNotIn('id', array_merge($excludeIds, $candidateIds))
                ->inRandomOrder()
                ->limit($needed)
                ->pluck('id')
                ->all();
            $candidateIds = array_merge($candidateIds, $fallbackIds);
        }

        if (empty($candidateIds)) {
            return collect();
        }

        $mutualCounts = $this->mutualCountsFor($candidateIds, $myFriendIds);

        $users = User::whereIn('id', $candidateIds)->get()->keyBy('id');

        return collect($candidateIds)
            ->map(function ($id) use ($users, $mutualCounts) {
                $user = $users->get($id);
                if ($user) {
                    $user->mutual_count = $mutualCounts[$id] ?? 0;
                }
                return $user;
            })
            ->filter()
            ->sortByDesc('mutual_count')
            ->values();
    }
}