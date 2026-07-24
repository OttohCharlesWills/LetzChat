<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Comment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'body',
        'is_edited',
    ];

    protected $casts = [
        'is_edited' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function (Comment $comment) {
            $comment->uuid = (string) Str::uuid();
        });

        // Keep posts.comments_count in sync automatically — counts replies
        // too (a reply is still a comment on the post), matching how
        // Facebook's total comment count includes reply counts.
        static::created(function (Comment $comment) {
            $comment->post()->increment('comments_count');
        });

        static::deleted(function (Comment $comment) {
            $comment->post()->decrement('comments_count');
        });
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Direct replies to this comment (one level — replies-to-replies still
     * point at this same comment via parent_id, so this naturally flattens
     * into a single reply list per top-level comment, like Facebook does).
     */
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id')->orderBy('created_at');
    }

    public function reactions()
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }

    public function isLikedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->reactions()->where('user_id', $user->id)->exists();
    }

    public function isReactedToBy(?User $user): ?string
    {
        if (!$user) {
            return null;
        }

        return $this->reactions()->where('user_id', $user->id)->value('type');
    }
}