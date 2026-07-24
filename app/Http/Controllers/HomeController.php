<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $viewer = $request->user();

        $posts = Post::with('user')
            ->visibleTo($viewer)
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate(10);

        $friends = $viewer->friends();

        return view('home', compact('posts', 'friends'));
    }
}
