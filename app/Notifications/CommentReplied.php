<?php

namespace App\Notifications;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Notifications\Notification;

class CommentReplied extends Notification
{
    public function __construct(
        public User $actor,
        public Post $post,
        public Comment $reply,
        public Comment $parentComment
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'comment_replied',
            'post_uuid' => $this->post->uuid,
            'comment_id' => $this->reply->id,
            'parent_comment_id' => $this->parentComment->id,
            'actor_id' => $this->actor->id,
            'actor_name' => $this->actor->first_name.' '.$this->actor->last_name,
            'actor_photo' => $this->actor->profile_photo,
            'actor_uuid' => $this->actor->uuid,
            'excerpt' => str($this->reply->body)->limit(80)->toString(),
            'message' => "{$this->actor->first_name} replied to your comment",
        ];
    }
}