<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Reaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Friendship;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function index(Request $request)
{
    $viewer = $request->user();

    // Tier 1: direct friends (accepted friendship, either direction)
    $friendIds = Friendship::accepted()
        ->involvingUser($viewer->id)
        ->get()
        ->map(fn ($f) => $f->requester_id === $viewer->id ? $f->addressee_id : $f->requester_id)
        ->unique()
        ->values();

    // Tier 2: friends-of-friends — friends of everyone in $friendIds,
    // excluding the viewer themself and anyone already a direct friend.
    $friendOfFriendIds = collect();

    if ($friendIds->isNotEmpty()) {
        $friendOfFriendIds = Friendship::accepted()
            ->where(function ($q) use ($friendIds) {
                $q->whereIn('requester_id', $friendIds)
                  ->orWhereIn('addressee_id', $friendIds);
            })
            ->get()
            ->map(fn ($f) => $friendIds->contains($f->requester_id) ? $f->addressee_id : $f->requester_id)
            ->unique()
            ->reject(fn ($id) => $id === $viewer->id || $friendIds->contains($id))
            ->values();
    }

    // Build a SQL CASE expression to assign each post a feed "tier":
    // 0 = viewer's own post, 1 = friend, 2 = friend-of-friend, 3 = everyone else.
    $friendList = $friendIds->map(fn ($id) => (int) $id)->implode(',') ?: '0';
    $fofList = $friendOfFriendIds->map(fn ($id) => (int) $id)->implode(',') ?: '0';

    $posts = Post::with(['user', 'images', 'videos'])
        ->visibleTo($viewer)
        ->selectRaw("posts.*, CASE
            WHEN posts.user_id = ? THEN 0
            WHEN posts.user_id IN ({$friendList}) THEN 1
            WHEN posts.user_id IN ({$fofList}) THEN 2
            ELSE 3
        END as feed_rank", [$viewer->id])
        ->orderBy('feed_rank')
        ->orderByDesc('is_pinned')
        ->orderByDesc('created_at')
        ->paginate(10);

    $friends = $viewer->friends();

    return view('posts.index', compact('posts', 'friends'));
}


public function storeInGroup(Request $request, \App\Models\Group $group)
{
    $validated = $request->validate([
        'body' => ['nullable', 'string', 'max:5000'],
        'images' => ['nullable', 'array', 'max:10'],
        'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
    ]);

    if (empty($validated['body']) && ! $request->hasFile('images')) {
        return response()->json([
            'message' => __('A post needs either text or at least one image.'),
        ], 422);
    }

    $user = $request->user();

    // Look up this user's role in the group directly — no model helper,
    // no indirection, straight from the DB.
    $role = \App\Models\GroupMember::where('group_id', $group->id)
        ->where('user_id', $user->id)
        ->value('role');

    if (! $role) {
        return response()->json([
            'message' => __('You must be a member of this group to post.'),
        ], 403);
    }

    $isAdminOrModerator = in_array($role, ['admin', 'moderator'], true);

    if ($group->post_permission === 'admin_only' && ! $isAdminOrModerator) {
        return response()->json([
            'message' => __('You do not have permission to post in this group.'),
        ], 403);
    }

    // Decide status directly, inline — no requiresApprovalFor() call.
    if ($isAdminOrModerator) {
        $status = 'published';
    } elseif ($group->post_permission === 'everyone' && $group->require_post_approval) {
        $status = 'pending';
    } else {
        $status = 'published';
    }

    $post = $user->posts()->create([
        'body' => $validated['body'] ?? null,
        'visibility' => 'public',
        'group_id' => $group->id,
        'status' => $status,
    ]);

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $index => $file) {
            $path = $file->store('posts', 'cloudinary');
            $url = Storage::disk('cloudinary')->url($path);

            $post->images()->create([
                'url' => $url,
                'public_id' => $path,
                'position' => $index,
            ]);
        }
    }

    $post->load(['user', 'images']);

    return response()->json([
        'message' => $status === 'pending'
            ? __('Your post was submitted and is awaiting admin approval.')
            : __('Your post has been shared.'),
        'html' => $status === 'published'
            ? view('posts.partials.post-card', ['post' => $post])->render()
            : null,
        'id' => $post->uuid,
        'status' => $status,
        // Debug fields — remove once confirmed working
        '_debug_role' => $role,
        '_debug_post_permission' => $group->post_permission,
        '_debug_require_post_approval' => $group->require_post_approval,
    ]);
}


public function store(Request $request)
{
    $validated = $request->validate([
        'body' => ['nullable', 'string', 'max:5000'],
        'visibility' => ['required', 'in:public,friends,custom,private'],
        'excluded_user_ids' => ['nullable', 'array'],
        'excluded_user_ids.*' => ['exists:users,id'],
        'images' => ['nullable', 'array', 'max:10'],
        'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        'group_id' => ['nullable', 'exists:groups,id'],
    ]);

    if (empty($validated['body']) && ! $request->hasFile('images')) {
        return response()->json([
            'message' => __('A post needs either text or at least one image.'),
        ], 422);
    }

    $status = 'published';

    if (! empty($validated['group_id'])) {
        $group = \App\Models\Group::findOrFail($validated['group_id']);

        if (! $group->allowsPostingBy($request->user())) {
            return response()->json([
                'message' => __('You do not have permission to post in this group.'),
            ], 403);
        }

        if ($group->requiresApprovalFor($request->user())) {
            $status = 'pending';
        }

        // Group posts are governed by group membership, not the
        // friends/public visibility system — force it regardless of input.
        $validated['visibility'] = 'public';
    }

    $post = $request->user()->posts()->create([
        'body' => $validated['body'] ?? null,
        'visibility' => $validated['visibility'],
        'group_id' => $validated['group_id'] ?? null,
        'status' => $status,
    ]);

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $index => $file) {
            $path = $file->store('posts', 'cloudinary');
            $url = Storage::disk('cloudinary')->url($path);

            $post->images()->create([
                'url' => $url,
                'public_id' => $path,
                'position' => $index,
            ]);
        }
    }

    if ($validated['visibility'] === 'custom' && ! empty($validated['excluded_user_ids'])) {
        $post->excludedUsers()->sync($validated['excluded_user_ids']);
    }

    $post->load(['user', 'images']);

    if ($request->wantsJson()) {
        return response()->json([
            'message' => $status === 'pending'
                ? __('Your post was submitted and is awaiting admin approval.')
                : __('Your post has been shared.'),
            'html' => $status === 'published'
                ? view('posts.partials.post-card', ['post' => $post])->render()
                : null,
            'id' => $post->uuid,
            'status' => $status,
        ]);
    }

    return back()->with('status', $status === 'pending'
        ? __('Your post was submitted and is awaiting admin approval.')
        : __('Your post has been shared.'));
}



public function storeVideo(Request $request, Post $post)
{
    // Only the post owner can attach a video to their own post
    abort_unless($post->user_id === $request->user()->id, 403);

    $validated = $request->validate([
        'video' => ['required', 'file', 'mimes:mp4,mov,webm', 'max:102400'], // 100MB cap
        'type' => ['nullable', 'in:video,reel'],
    ]);

    $file = $request->file('video');
    $type = $validated['type'] ?? 'video';

    $folder = $type === 'reel' ? 'posts/reels' : 'posts/videos';
    $path = $file->store($folder, 'backblaze');

    $postVideo = $post->videos()->create([
        'type' => $type,
        'path' => $path,
        'original_name' => $file->getClientOriginalName(),
        'size_bytes' => $file->getSize(),
        'position' => $post->videos()->count(),
    ]);

    if ($request->wantsJson()) {
        return response()->json([
            'message' => __('Video uploaded.'),
            'video' => [
                'id' => $postVideo->id,
                'type' => $postVideo->type,
                'url' => $postVideo->url(),
            ],
        ]);
    }

    return back()->with('status', __('Video uploaded.'));
}

    /**
     * Delete a post (soft delete). Author only.
     */
    public function destroy(Request $request, Post $post)
    {
        abort_unless($post->user_id === $request->user()->id, 403);

        $post->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => __('Post deleted.')]);
        }

        return back()->with('status', __('Post deleted.'));
    }

    /**
     * React to a post. Left-click sends no type (defaults to 'like');
     * the right-click picker sends an explicit type. Reacting again with
     * the SAME type you already have removes it (unlike); a different
     * type just updates your existing reaction.
     */
    public function react(Request $request, Post $post)
    {
        $validated = $request->validate([
            'type' => ['nullable', 'in:like,love,haha,wow,sad,angry'],
        ]);
        $type = $validated['type'] ?? 'like';

        $existing = $post->reactions()->where('user_id', $request->user()->id)->first();

        if ($existing && $existing->type === $type) {
            $existing->delete();
            $current = null;
        } elseif ($existing) {
            $existing->update(['type' => $type]);
            $current = $type;
        } else {
            $post->reactions()->create(['user_id' => $request->user()->id, 'type' => $type]);
            $current = $type;
        }

        if ($current && $post->user_id !== $request->user()->id) {
            $post->user->notify(new \App\Notifications\PostReacted($request->user(), $post, $current));
        }

        $post->refresh();

        return response()->json([
            'likes_count'      => $post->likes_count,
            'current_reaction' => $current,
            'current_emoji'    => $current ? Reaction::emojiFor($current) : null,
        ]);
    }

    /**
     * Everyone who reacted to this post, with their name and which reaction
     * type they used. Powers the hover tooltip on the emoji badge cluster —
     * fetched lazily (once per post, cached client-side) rather than eagerly
     * loaded with every feed page.
     */
    public function reactors(Post $post)
    {
        $reactors = $post->reactions()
            ->with('user:id,first_name,last_name')
            ->get()
            ->map(fn ($r) => [
                'name' => $r->user->first_name.' '.$r->user->last_name,
                'type' => $r->type,
            ]);

        return response()->json(['reactors' => $reactors]);
    }

    /**
     * Load a post's top-level comments (with their one level of replies).
     * Fetched lazily when the Comment button is first clicked, not eagerly
     * on every feed page load.
     */
    public function comments(Request $request, Post $post)
    {
        $comments = $post->comments()
            ->with(['user', 'replies.user'])
            ->get();

        $html = $comments
            ->map(fn ($comment) => view('posts.partials.comment', [
                'comment' => $comment,
                'post'    => $post,
            ])->render())
            ->implode('');

        return response()->json(['html' => $html]);
    }

    /**
     * Add a comment, or a reply if parent_id is present.
     */
    public function storeComment(Request $request, Post $post)
    {
        abort_unless($post->comments_enabled, 403, 'Comments are turned off for this post.');

        $validated = $request->validate([
            'body'      => ['required', 'string', 'max:2000'],
            'parent_id' => ['nullable', 'exists:comments,id'],
        ]);

        $comment = $post->allComments()->create([
            'user_id'   => $request->user()->id,
            'parent_id' => $validated['parent_id'] ?? null,
            'body'      => $validated['body'],
        ]);

        $comment->load('user');

        if ($comment->parent_id) {
            $parentComment = $comment->parent;
            if ($parentComment && $parentComment->user_id !== $request->user()->id) {
                $parentComment->user->notify(
                    new \App\Notifications\CommentReplied($request->user(), $post, $comment, $parentComment)
                );
            }
        } elseif ($post->user_id !== $request->user()->id) {
            $post->user->notify(new \App\Notifications\PostCommented($request->user(), $post, $comment));
        }

        return response()->json([
            'comments_count' => $post->fresh()->comments_count,
            'is_reply'       => (bool) $comment->parent_id,
            'parent_id'      => $comment->parent_id,
            'html'           => view('posts.partials.comment', [
                'comment' => $comment,
                'post'    => $post,
            ])->render(),
        ]);
    }

    /**
     * Delete your own comment (soft delete — see Comment migration notes
     * on why: replies underneath stay intact and show under a
     * "This comment was deleted" placeholder).
     */
    public function destroyComment(Request $request, Comment $comment)
    {
        abort_unless($comment->user_id === $request->user()->id, 403);

        $post = $comment->post;
        $comment->delete();

        return response()->json([
            'comments_count' => $post->fresh()->comments_count,
        ]);
    }

    /**
     * Simple like toggle on a COMMENT (not the full 6-emoji picker —
     * that's only on posts per the design). Same Reaction model either way.
     */
    public function reactComment(Request $request, Comment $comment)
    {
        $existing = $comment->reactions()->where('user_id', $request->user()->id)->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            $comment->reactions()->create(['user_id' => $request->user()->id, 'type' => 'like']);
            $liked = true;
        }

        return response()->json([
            'likes_count' => $comment->fresh()->likes_count,
            'liked'       => $liked,
        ]);
    }

    /**
     * Share/repost a post, with an optional caption. This creates a NEW
     * post row pointing back at the original via shared_post_id — so it
     * lives in the feed like any other post and can get its own comments
     * and reactions.
     */
    public function share(Request $request, Post $post)
    {
        $validated = $request->validate([
            'caption'    => ['nullable', 'string', 'max:2000'],
            'visibility' => ['nullable', 'in:public,friends,custom,private'],
        ]);

        $share = $request->user()->posts()->create([
            'body'           => $validated['caption'] ?? null,
            'visibility'     => $validated['visibility'] ?? 'public',
            'shared_post_id' => $post->id,
        ]);

        $share->load(['user', 'sharedPost.user']);

        return response()->json([
            'shares_count' => $post->fresh()->shares_count,
            'html'         => view('posts.partials.post-card', ['post' => $share])->render(),
        ]);
    }

    /**
     * Owner-only: toggle whether new comments can be added to this post.
     */
    public function toggleComments(Request $request, Post $post)
    {
        abort_unless($post->user_id === $request->user()->id, 403);

        $post->update(['comments_enabled' => ! $post->comments_enabled]);

        return response()->json(['comments_enabled' => $post->comments_enabled]);
    }
}