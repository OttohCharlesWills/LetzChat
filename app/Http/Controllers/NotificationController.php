<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Full notifications page.
     */
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->paginate(20);

        return view('notifications.index', compact('notifications'));
    }
    /**
     * Small recent-list payload for the navbar bell dropdown —
     * latest 8 + the unread count, in one round trip.
     */
    public function dropdown(Request $request)
    {
        $notifications = $request->user()->notifications()->latest()->take(8)->get();
        $unreadCount = $request->user()->unreadNotifications()->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'html' => view('notifications.partials.list', compact('notifications'))->render(),
        ]);
    }

    
    public function unreadCount(Request $request)
    {
        return response()->json([
            'count' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, DatabaseNotification $notification)
    {
        abort_unless($notification->notifiable_id === $request->user()->id, 403);

        $notification->markAsRead();

        return response()->json(['message' => 'ok']);
    }

    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json(['message' => 'ok']);
    }
    
}