<?php

namespace App\Http\Controllers;

use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $recentPosts = $user->posts()
            ->withCount('images')
            ->orderByDesc('created_at')
            ->take(10)
            ->get(['id', 'uuid', 'body', 'views_count', 'likes_count', 'comments_count', 'shares_count', 'created_at']);

        $totalPosts = $user->posts()->count();
        $totalViews = $user->posts()->sum('views_count');
        $totalLikes = $user->posts()->sum('likes_count');
        $totalComments = $user->posts()->sum('comments_count');

        $postIds = $user->posts()->pluck('id');

        $topRegions = collect();

        if ($postIds->isNotEmpty()) {
            $topRegions = UserActivity::query()
                ->join('users', 'users.id', '=', 'user_activities.user_id')
                ->where('user_activities.event_name', 'post_view')
                ->whereIn(DB::raw("JSON_EXTRACT(user_activities.metadata, '$.post_id')"), $postIds)
                ->whereNotNull('users.location')
                ->select('users.location', DB::raw('COUNT(*) as views'))
                ->groupBy('users.location')
                ->orderByDesc('views')
                ->take(5)
                ->get();
        }

        $tasks = [
            [
                'label' => __('Upload a profile picture'),
                'done'  => (bool) $user->profile_photo,
                'route' => route('profile.show', ['user' => $user->id]),
            ],
            [
                'label' => __('Upload a cover photo'),
                'done'  => (bool) $user->cover_photo,
                'route' => route('profile.show', ['user' => $user->id]),
            ],
            [
                'label' => __('Join a group'),
                'done'  => $user->groups()->isNotEmpty(),
                'route' => route('groups.index'),
            ],
        ];

        return view('dashboard.index', compact(
            'recentPosts', 'totalPosts', 'totalViews', 'totalLikes', 'totalComments', 'topRegions', 'tasks'
        ));
    }
}