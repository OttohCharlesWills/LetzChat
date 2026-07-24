<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Public profile page: /{username}
     *
     * Note: User's getRouteKeyName() is 'uuid' (used elsewhere for
     * /friends/request/{user} etc.), but profile URLs need to be
     * pretty (/soniasocials), so we deliberately look this up by
     * username manually instead of relying on implicit route binding.
     */
    public function show(string $username)
    {
        $user = User::where('username', $username)->firstOrFail();

        $isOwnProfile = Auth::check() && Auth::id() === $user->id;

        // Whether the CURRENT VIEWER already follows this profile's owner —
        // powers the Follow/Following button in the identity section,
        // same pattern used on the post card.
        $isFollowing = !$isOwnProfile && Auth::check() && $user->isFollowedBy(Auth::user());

        $friends = $user->friends();
        $friendsCount = $friends->count();
        $friendsPreview = $friends->take(6);

        $postsQuery = \App\Models\Post::where('user_id', $user->id)
            ->with('user')
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at');

        if (!$isOwnProfile) {
            // Strangers/guests only ever see 'public' posts. If the viewer is
            // an accepted friend of the profile owner, they also see 'friends'
            // visibility posts. 'private' and 'custom' never show here unless
            // you're the owner — 'custom' would need its own audience-list
            // check once that feature actually exists.
            $viewerIsFriend = Auth::check() && $user->isFriendsWith(Auth::user());

            $postsQuery->where(function ($q) use ($viewerIsFriend) {
                $q->where('visibility', 'public');
                if ($viewerIsFriend) {
                    $q->orWhere('visibility', 'friends');
                }
            });
        }

        $posts = $postsQuery->paginate(10);

        return view('profile.show', compact(
            'user',
            'isOwnProfile',
            'isFollowing',
            'friendsCount',
            'friendsPreview',
            'posts'
        ));
    }

    /**
     * Edit form for the currently authenticated user's own profile.
     */
    public function edit()
    {
        $user = Auth::user();

        return view('profile.edit', compact('user'));
    }

    /**
     * Save changes to the currently authenticated user's own profile.
     * Avatar/cover photo upload is handled separately — not in this step.
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'first_name'    => ['required', 'string', 'max:255'],
            'last_name'     => ['required', 'string', 'max:255'],
            'username'      => [
                'required', 'string', 'max:50',
                'regex:/^[A-Za-z0-9_.]+$/',
                Rule::unique('users', 'username')->ignore($user->id),
            ],
            'bio'           => ['nullable', 'string', 'max:1000'],
            'location'      => ['nullable', 'string', 'max:255'],
            'education'     => ['nullable', 'string', 'max:255'],
            'gender'        => ['nullable', 'in:male,female,other'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'phone'         => [
                'nullable', 'string', 'max:20',
                Rule::unique('users', 'phone')->ignore($user->id),
            ],
        ]);

        $user->update([
            'first_name'    => $validated['first_name'],
            'last_name'     => $validated['last_name'],
            'username'      => strtolower($validated['username']),
            'bio'           => $validated['bio'] ?? null,
            'location'      => $validated['location'] ?? null,
            'education'     => $validated['education'] ?? null,
            'gender'        => $validated['gender'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'phone'         => $validated['phone'] ?? null,
        ]);

        return redirect()
            ->route('profile.show', $user->username)
            ->with('status', 'Profile updated.');
    }

    /**
     * Upload/replace the logged-in user's profile picture.
     * Saves to the `profile_photo` column (kept separate from the legacy
     * `avatar` column for now — we'll reconcile the two once this is
     * confirmed working end-to-end).
     *
     * Note: this installed version of cloudinary-labs/cloudinary-laravel
     * (v3.0.2) only registers a plain Flysystem "cloudinary" disk — it
     * does NOT provide a storeOnCloudinary() macro (that only existed in
     * older v1.x/v2.x releases the online tutorials were written against).
     * So this just uses Storage::disk('cloudinary') like any other disk.
     */
    public function updateProfilePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $path = $request->file('profile_photo')->store('profile_photos', 'cloudinary');
        $url = Storage::disk('cloudinary')->url($path);

        $user = Auth::user();
        $user->profile_photo = $url;
        $user->save();

        return response()->json([
            'message' => __('Profile picture updated.'),
            'url' => $user->profile_photo,
        ]);
    }

    /**
     * Upload/replace the logged-in user's cover photo.
     */
    public function updateCoverPhoto(Request $request)
    {
        $request->validate([
            'cover_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);

        $path = $request->file('cover_photo')->store('cover_photos', 'cloudinary');
        $url = Storage::disk('cloudinary')->url($path);

        $user = Auth::user();
        $user->cover_photo = $url;
        $user->save();

        return response()->json([
            'message' => __('Cover photo updated.'),
            'url' => $user->cover_photo,
        ]);
    }
}