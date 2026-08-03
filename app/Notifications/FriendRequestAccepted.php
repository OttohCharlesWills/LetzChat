<?php

namespace App\Notifications;

use App\Models\Friendship;
use Illuminate\Notifications\Notification;

class FriendRequestAccepted extends Notification
{
    public function __construct(public Friendship $friendship) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $accepter = $this->friendship->addressee;

        return [
            'type' => 'friend_request_accepted',
            'friendship_id' => $this->friendship->id,
            'actor_id' => $accepter->id,
            'actor_name' => $accepter->first_name.' '.$accepter->last_name,
            'actor_photo' => $accepter->profile_photo,
            'actor_uuid' => $accepter->uuid,
            'message' => "{$accepter->first_name} accepted your friend request",
        ];
    }
}