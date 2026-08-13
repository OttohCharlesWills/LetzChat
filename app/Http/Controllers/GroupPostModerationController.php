<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Post;
use Illuminate\Http\Request;

class GroupPostModerationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * List pending posts for a group. Admin/moderator only.
     */
        public function index(Request $request, Group $group)
        {
            abort_unless($group->isAdminOrModerator($request->user()), 403);

            $pendingPosts = Post::with(['user', 'images', 'videos'])
                ->pendingIn($group->id)
                ->orderBy('created_at')
                ->get();

            $isAdmin = $group->isAdmin($request->user());

            return view('groups.pending-posts', compact('group', 'pendingPosts', 'isAdmin'));
        }

    public function approve(Request $request, Group $group, Post $post)
    {
        abort_unless($group->isAdminOrModerator($request->user()), 403);
        abort_unless($post->group_id === $group->id, 404);

        $post->update(['status' => 'published']);

        return response()->json(['message' => __('Post approved.')]);
    }

    public function reject(Request $request, Group $group, Post $post)
    {
        abort_unless($group->isAdminOrModerator($request->user()), 403);
        abort_unless($post->group_id === $group->id, 404);

        $post->delete(); // soft delete, per your posts table's softDeletes()

        return response()->json(['message' => __('Post rejected and removed.')]);
    }
}