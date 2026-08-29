<?php

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Post;
use Illuminate\Support\Str;
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

        // Creator is the group's one and only admin — this role can't be
        // taken by anyone else except via an explicit transferAdmin() call.
        GroupMember::create([
            'group_id' => $group->id,
            'user_id'  => $request->user()->id,
            'role'     => 'admin',
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

    /**
     * Leave the group.
     *
     * - Regular member / moderator: leaves immediately.
     * - Admin: CANNOT just leave. They must first transfer adminship to
     *   someone else (see transferAdmin()), or delete the group entirely
     *   (see destroy()). This keeps a group from ever being left admin-less.
     */
    public function leave(Request $request, Group $group)
    {
        $membership = $group->members()->where('user_id', $request->user()->id)->first();

        if (! $membership) {
            return back()->with('status', __('You are not a member of :name.', ['name' => $group->name]));
        }

        abort_if(
            $membership->role === 'admin',
            403,
            'As the group owner, you can\'t leave directly. Transfer ownership to another member first, or delete the group instead.'
        );

        $membership->delete();

        $group->syncConversationParticipants();

        return back()->with('status', __('You left :name.', ['name' => $group->name]));
    }

    /**
     * Delete the group entirely. Admin only — this is the other valid way
     * for an admin to walk away, besides transferring adminship first.
     */
    public function destroy(Request $request, Group $group)
        {
            abort_unless($group->isAdmin($request->user()), 403);

            $group->delete();

            return redirect()->route('groups.index')->with('status', __(':name was deleted.', ['name' => $group->name]));
        }

    /**
     * Transfer the admin role to another member. Only the current admin can
     * do this. The old admin becomes a regular member; the group still only
     * ever has exactly one admin at a time.
     */
    public function transferAdmin(Request $request, Group $group, \App\Models\User $user)
    {
        $actor = $request->user();

        abort_unless($group->isAdmin($actor), 403);
        abort_if($user->id === $actor->id, 403, 'You are already the admin.');

        $newAdminMembership = $group->members()->where('user_id', $user->id)->first();

        abort_unless($newAdminMembership, 404, 'That user is not a member of this group.');

        $currentAdminMembership = $group->members()->where('user_id', $actor->id)->first();

        $newAdminMembership->update(['role' => 'admin']);
        $currentAdminMembership?->update(['role' => 'member']);

        $group->syncConversationParticipants();

        return response()->json([
            'message' => __(':name is now the admin of :group.', ['name' => $user->first_name, 'group' => $group->name]),
        ]);
    }

    /**
     * Admin/moderator removes another member from the group (a "kick").
     * Distinct from leave() — this is other-initiated, not self-initiated.
     *
     * Rules:
     * - Admin or moderator only.
     * - Can't kick yourself — use "Leave group" for that.
     * - The admin (group creator) can NEVER be kicked, by anyone, under any
     *   condition. They can only be replaced via transferAdmin(), or the
     *   group can be deleted via destroy().
     */
    public function removeMember(Request $request, Group $group, \App\Models\User $user)
    {
        $actor = $request->user();

        abort_unless($group->isAdminOrModerator($actor), 403);
        abort_if($user->id === $actor->id, 403, "Use \"Leave group\" to remove yourself.");

        $membership = $group->members()->where('user_id', $user->id)->first();

        abort_unless($membership, 404, 'That user is not a member of this group.');

        abort_if(
            $membership->role === 'admin',
            403,
            'The group owner can\'t be removed. They can only leave by transferring ownership or deleting the group.'
        );

        $membership->delete();

        $group->syncConversationParticipants();

        return response()->json([
            'message' => __(':name was removed from :group.', ['name' => $user->first_name, 'group' => $group->name]),
        ]);
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

    public function join(Request $request, Group $group)
    {
        $userId = $request->user()->id;

        if ($group->isMember($request->user())) {
            return back()->with('status', __('You are already a member.'));
        }

        if ($group->requiresJoinApproval()) {
            \App\Models\GroupJoinRequest::updateOrCreate(
                ['group_id' => $group->id, 'user_id' => $userId],
                ['status' => 'pending']
            );

            return back()->with('status', __('Your request to join :name is awaiting approval.', ['name' => $group->name]));
        }

        GroupMember::firstOrCreate([
            'group_id' => $group->id,
            'user_id'  => $userId,
        ], [
            'role' => 'member',
        ]);

        $group->syncConversationParticipants();

        return back()->with('status', __('Joined :name.', ['name' => $group->name]));
    }

    public function joinRequests(Request $request, Group $group)
    {
        abort_unless($group->isAdminOrModerator($request->user()), 403);

        $requests = $group->joinRequests()
            ->where('status', 'pending')
            ->with('user')
            ->orderBy('created_at')
            ->get();

        $isAdmin = $group->isAdmin($request->user());

        return view('groups.join-requests', compact('group', 'requests', 'isAdmin'));
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

        $group->syncConversationParticipants();

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