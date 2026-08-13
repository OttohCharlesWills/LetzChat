<?php

namespace App\Notifications;

use App\Models\Friendship;
use Illuminate\Notifications\Notification;

class FriendRequestReceived extends Notification
{
    public function __construct(public Friendship $friendship) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $requester = $this->friendship->requester;

        return [
            'type' => 'friend_request_received',
            'friendship_id' => $this->friendship->id,
            'actor_id' => $requester->id,
            'actor_name' => $requester->first_name.' '.$requester->last_name,
            'actor_photo' => $requester->profile_photo,
            'actor_uuid' => $requester->uuid,
            'message' => $requester->first_name . ' ' . $requester->last_name . ' sent you a friend request',
        ];
    }
}