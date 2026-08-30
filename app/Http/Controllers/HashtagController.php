<?php

namespace App\Http\Controllers;

use App\Models\Hashtag;
use Illuminate\Http\Request;

class HashtagController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function show(Request $request, string $hashtag)
    {
        $tag = Hashtag::where('name', mb_strtolower($hashtag))->firstOrFail();

        $posts = $tag->posts()
            ->with(['user', 'images', 'videos'])
            ->visibleTo($request->user())
            ->published()
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('hashtags.show', compact('tag', 'posts'));
    }
}