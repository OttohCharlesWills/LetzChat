<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Notifications\Notification;

class PostCommented extends Notification
{
    public function __construct(
        public User $actor,
        public Post $post,
        public Comment $comment
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'post_commented',
            'post_uuid' => $this->post->uuid,
            'comment_id' => $this->comment->id,
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->first_name.' '.$this->actor->last_name,
            'actor_photo' => $this->actor->profile_photo,
            'actor_uuid' => $this->actor->uuid,
            'excerpt' => str($this->comment->body)->limit(80)->toString(),
            'message' => "{$this->actor->first_name} commented on your post",
        ];
    }
}