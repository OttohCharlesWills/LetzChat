<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\BirthdayController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\GroupPostModerationController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// USERNAME CHECKING
Route::get('/check-username', function (\Illuminate\Http\Request $request) {
    $username = $request->query('username');

    $taken = DB::table('users')->where('username', $username)->exists();

    return response()->json(['available' => !$taken]);
});


// FRIENDS ROUTES
Route::middleware('auth')->prefix('friends')->name('friends.')->group(function () {
    Route::get('/', [FriendController::class, 'index'])->name('index');
    Route::get('/requests', [FriendController::class, 'requestsPage'])->name('requests');
    Route::get('/suggestions', [FriendController::class, 'suggestionsPage'])->name('suggestions');
    Route::get('/list', [FriendController::class, 'allFriends'])->name('all');

    Route::post('/request/{user}', [FriendController::class, 'sendRequest'])->name('request');
    Route::post('/accept/{friendship}', [FriendController::class, 'accept'])->name('accept');
    Route::post('/decline/{friendship}', [FriendController::class, 'decline'])->name('decline');
    Route::delete('/{friendship}', [FriendController::class, 'destroy'])->name('destroy');

    Route::get('/birthdays', [BirthdayController::class, 'index'])->name('birthdays');

    Route::post('/birthdays/{friend}/message', [BirthdayController::class, 'sendMessage'])->name('birthdays.message');

});


// CHAT ROUTES

Route::middleware('auth')->prefix('chat')->name('chat.')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('index');
    Route::post('/start/{user}', [ChatController::class, 'startOrOpen'])->name('start');
    Route::get('/{conversation}', [ChatController::class, 'show'])->name('show');
    Route::post('/{conversation}/messages', [ChatController::class, 'sendMessage'])->name('send');
Route::post('/{conversation}/voice', [ChatController::class, 'sendVoiceNote'])->name('voice');
});


// POST ROUTES 
Route::get('/posts', [PostController::class, 'index'])->name('posts.index');
Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
Route::get('/posts/{post}/reactors', [PostController::class, 'reactors'])->name('posts.reactors');


Route::post('/posts/{post}/react', [PostController::class, 'react'])->name('posts.react');
Route::patch('/posts/{post}/toggle-comments', [PostController::class, 'toggleComments'])->name('posts.toggle-comments');
Route::post('/posts/{post}/share', [PostController::class, 'share'])->name('posts.share');

Route::get('/posts/{post}/comments', [PostController::class, 'comments'])->name('posts.comments.index');
Route::post('/posts/{post}/comments', [PostController::class, 'storeComment'])->name('posts.comments.store');
Route::delete('/comments/{comment}', [PostController::class, 'destroyComment'])->name('comments.destroy');
Route::post('/comments/{comment}/react', [PostController::class, 'reactComment'])->name('comments.react');

Route::post('/posts/{post}/videos', [PostController::class, 'storeVideo'])->name('posts.videos.store');



// PROFILE ROUTE LAST ROUTES

Route::middleware('auth')->group(function () {
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});


// ROUTES FOR FOLLOWERS
Route::middleware('auth')->group(function () {
    Route::post('/follow/{user}', [FollowController::class, 'follow'])->name('follow.store');
    Route::delete('/follow/{user}', [FollowController::class, 'unfollow'])->name('follow.destroy');
});

Route::get('/{username}/followers', [FollowController::class, 'followers'])->name('profile.followers');
Route::get('/{username}/following', [FollowController::class, 'following'])->name('profile.following');


// ROUTES FOR PROFILE AND COVER PICTURE
Route::post('/profile/photo', [ProfileController::class, 'updateProfilePhoto'])->name('profile.photo.update');
 
Route::post('/profile/cover', [ProfileController::class, 'updateCoverPhoto'])->name('profile.cover.update');


// ROUTES FOR GGROUP


Route::middleware('auth')->prefix('groups')->name('groups.')->group(function () {
    Route::get('/', [GroupController::class, 'index'])->name('index');
    Route::get('/create', [GroupController::class, 'create'])->name('create');
    Route::post('/', [GroupController::class, 'store'])->name('store');
    Route::get('/{group}', [GroupController::class, 'show'])->name('show');
    // Route::post('/{group}/join', [GroupController::class, 'join'])->name('join');
    Route::post('/{group}/leave', [GroupController::class, 'leave'])->name('leave');
    Route::post('/{group}/cover', [GroupController::class, 'updateCoverPhoto'])->name('cover.update');
});

Route::middleware('auth')->prefix('groups/{group}')->name('groups.')->group(function () {
    Route::get('/settings', [GroupController::class, 'settings'])->name('settings');
    Route::patch('/settings', [GroupController::class, 'updateSettings'])->name('settings.update');

    Route::get('/join-requests', [GroupController::class, 'joinRequests'])->name('join-requests.index');
    Route::patch('/join-requests/{joinRequest}/approve', [GroupController::class, 'approveJoinRequest'])->name('join-requests.approve');
    Route::delete('/join-requests/{joinRequest}/reject', [GroupController::class, 'rejectJoinRequest'])->name('join-requests.reject');
});


// NOTIFICATION ROUTES

Route::middleware('auth')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/dropdown', [NotificationController::class, 'dropdown'])->name('notifications.dropdown');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
});



// GROUP MODERATION ROUTES
Route::middleware('auth')->prefix('groups/{group}')->name('groups.')->group(function () {
    Route::get('/pending-posts', [GroupPostModerationController::class, 'index'])->name('pending-posts.index');
    Route::patch('/pending-posts/{post}/approve', [GroupPostModerationController::class, 'approve'])->name('pending-posts.approve');
    Route::delete('/pending-posts/{post}/reject', [GroupPostModerationController::class, 'reject'])->name('pending-posts.reject');
});

Route::get('/my-groups/postable', [GroupController::class, 'postable'])->name('groups.postable');



// IMPORTANT: this must be the very LAST route in the file.
// Route::get('/{username}', [ProfileController::class, 'show'])
//     ->where('username', '[A-Za-z0-9_.]+')
//     ->name('profile.show');

Route::get('/profile/{user:uuid}', [ProfileController::class, 'show'])
    ->name('profile.show');