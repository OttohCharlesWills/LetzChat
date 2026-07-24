<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Follow a user. Counter columns (followers_count/following_count) are
     * kept in sync automatically by Follow::booted() — don't touch them here.
     */
    public function follow(User $user)
    {
        $followerId = Auth::id();

        if ($user->id === $followerId) {
            abort(403, "You can't follow yourself.");
        }

        Follow::firstOrCreate([
            'follower_id' => $followerId,
            'followed_id' => $user->id,
        ]);

        return back()->with('status', 'You are now following ' . $user->first_name . '.');
    }

    /**
     * Unfollow a user. Counters sync automatically via the model's
     * deleted() hook when this row is removed.
     */
    public function unfollow(User $user)
    {
        // ->each->delete() (not a bulk ->delete() query) so Eloquent
        // actually fires the deleted() hook that decrements the counters.
        Follow::where('follower_id', Auth::id())
            ->where('followed_id', $user->id)
            ->get()
            ->each
            ->delete();

        return back()->with('status', 'You unfollowed ' . $user->first_name . '.');
    }

    /**
     * List of a user's followers (people who follow them).
     */
    public function followers(string $username)
    {
        $user = User::where('username', $username)->firstOrFail();

        $followers = User::whereIn('id', function ($query) use ($user) {
                $query->select('follower_id')
                    ->from('follows')
                    ->where('followed_id', $user->id);
            })
            ->paginate(24);

        return view('profile.followers', compact('user', 'followers'));
    }

    /**
     * List of who a user is following.
     */
    public function following(string $username)
    {
        $user = User::where('username', $username)->firstOrFail();

        $following = User::whereIn('id', function ($query) use ($user) {
                $query->select('followed_id')
                    ->from('follows')
                    ->where('follower_id', $user->id);
            })
            ->paginate(24);

        return view('profile.following', compact('user', 'following'));
    }
}