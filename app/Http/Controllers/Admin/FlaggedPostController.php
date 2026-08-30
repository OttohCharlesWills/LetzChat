<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class FlaggedPostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            abort_unless($request->user()->is_admin, 403);
            return $next($request);
        });
    }

    public function index()
    {
        $posts = Post::with(['user', 'images', 'group'])
            ->flagged()
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.flagged-posts.index', compact('posts'));
    }

    /**
     * Looks fine on review — clear the flag, post stays up as-is.
     */
    public function dismiss(Post $post)
    {
        $post->update(['is_flagged' => false, 'flagged_words' => null]);

        return back()->with('status', __('Flag dismissed. Post stays up.'));
    }

    /**
     * Confirmed violation — remove the post entirely.
     */
    public function removePost(Post $post)
    {
        $post->delete();

        return back()->with('status', __('Post deleted.'));
    }
}