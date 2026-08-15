<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Post;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Groups home: sidebar (Your feed / Discover / Your groups + groups you
     * manage) with a "Recent activity" feed of posts from groups you're in.
     * ?tab=feed (default) | discover | mine
     */
    public function index(Request $request)
    {
        $viewer = $request->user();
        $tab = $request->query('tab', 'feed');

        $myGroups = $viewer->groups();
        $managedGroups = $myGroups->filter(fn ($g) => $g->isAdmin($viewer))->values();

        $posts = collect();
        $discoverGroups = collect();

        if ($tab === 'discover') {
            $myGroupIds = $myGroups->pluck('id');
            $discoverGroups = Group::where('privacy', 'public')
                ->whereNotIn('id', $myGroupIds)
                ->orderByDesc('members_count')
                ->paginate(12);
        } else {
            // 'feed' and 'mine' both start from your groups' posts;
            // 'mine' view just emphasizes the group list over the feed.
            $groupIds = $myGroups->pluck('id');
            $posts = Post::whereIn('group_id', $groupIds)
                ->with(['user', 'group'])
                ->orderByDesc('created_at')
                ->paginate(10);
        }

        return view('groups.index', compact('tab', 'myGroups', 'managedGroups', 'posts', 'discoverGroups'));
    }

    public function create()
    {
        return view('groups.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'privacy'     => ['required', 'in:public,private'],
        ]);

        $group = Group::create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'privacy'     => $validated['privacy'],
            'created_by'  => $request->user()->id,
        ]);

        // Creator automatically becomes the first member, as admin.
        $group = Group::create([
            'uuid' => Str::uuid(),
            'name' => 'Developers',
            'privacy' => 'public',
            'join_approval' => 'automatic',
            'created_by' => 3,
        ]);

        GroupMember::create([
            'group_id' => $group->id,
            'user_id' => 3,
            'role' => 'admin',
        ]);

        return redirect()->route('groups.show', $group->uuid)->with('status', __('Group created!'));
    }

    public function show(Request $request, Group $group)
    {
        $viewer = $request->user();
        $isMember = $group->isMember($viewer);
        $isAdmin = $isMember && $group->isAdmin($viewer);

        abort_if($group->privacy === 'private' && !$isMember, 403);

        $posts = $group->posts()
            ->with('user')
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate(10);

        $memberPreview = $group->memberUsers()->take(8)->get();

        return view('groups.show', compact('group', 'isMember', 'isAdmin', 'posts', 'memberPreview'));
    }

    /**
     * Upload/replace this group's cover photo. Admin only.
     * Same Storage::disk('cloudinary') pattern as ProfileController — this
     * package version doesn't support the old Cloudinary::upload() facade call.
     */
    public function updateCoverPhoto(Request $request, Group $group)
    {
        abort_unless($group->isAdmin($request->user()), 403);

        $request->validate([
            'cover_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);

        $path = $request->file('cover_photo')->store('group_covers', 'cloudinary');
        $url = \Illuminate\Support\Facades\Storage::disk('cloudinary')->url($path);

        $group->update(['cover_photo' => $url]);

        return response()->json([
            'message' => __('Cover photo updated.'),
            'url' => $url,
        ]);
    }

    public function leave(Request $request, Group $group)
    {
        $membership = $group->members()->where('user_id', $request->user()->id)->first();

        if ($membership) {
            abort_if(
                $membership->role === 'admin' && $group->members()->where('role', 'admin')->count() === 1,
                403,
                'Promote another admin before leaving — a group can\'t be left with no admins.'
            );

            $membership->delete();
        }

        return back()->with('status', __('You left :name.', ['name' => $group->name]));
    }

    public function postable(Request $request)
    {
        $groups = $request->user()->groups()
            ->filter(fn ($group) => $group->allowsPostingBy($request->user()))
            ->map(fn ($group) => [
                'id' => $group->id,
                'name' => $group->name,
                'cover_photo' => $group->cover_photo,
            ])
            ->values();

        return response()->json(['groups' => $groups]);
    }

    public function joinRequests(Request $request, Group $group)
{
    abort_unless($group->isAdminOrModerator($request->user()), 403);

    $requests = $group->joinRequests()
        ->where('status', 'pending')
        ->with('user')
        ->orderBy('created_at')
        ->get();

    return view('groups.join-requests', compact('group', 'requests'));
}

public function approveJoinRequest(Request $request, Group $group, \App\Models\GroupJoinRequest $joinRequest)
{
    abort_unless($group->isAdminOrModerator($request->user()), 403);
    abort_unless($joinRequest->group_id === $group->id, 404);

    GroupMember::firstOrCreate([
        'group_id' => $group->id,
        'user_id'  => $joinRequest->user_id,
    ], ['role' => 'member']);

    $joinRequest->update(['status' => 'approved']);

    return response()->json(['message' => __('Request approved.')]);
}

public function rejectJoinRequest(Request $request, Group $group, \App\Models\GroupJoinRequest $joinRequest)
{
    abort_unless($group->isAdminOrModerator($request->user()), 403);
    abort_unless($joinRequest->group_id === $group->id, 404);

    $joinRequest->update(['status' => 'rejected']);

    return response()->json(['message' => __('Request rejected.')]);
}

public function settings(Request $request, Group $group)
{
    $isAdmin = $group->isAdmin($request->user());

    abort_unless($isAdmin, 403);

    return view('groups.settings', compact('group', 'isAdmin'));
}

public function updateSettings(Request $request, Group $group)
{
    abort_unless($group->isAdmin($request->user()), 403);

    $validated = $request->validate([
        'post_permission' => ['required', 'in:everyone,admin_only'],
        'require_post_approval' => ['nullable', 'boolean'],
        'join_approval' => ['required', 'in:automatic,manual'],
    ]);

    $group->update([
        'post_permission' => $validated['post_permission'],
        'require_post_approval' => $request->boolean('require_post_approval'),
        'join_approval' => $validated['join_approval'],
    ]);

    return back()->with('status', __('Group settings updated.'));
}
}