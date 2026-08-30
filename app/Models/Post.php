<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\PostImage;
use App\Models\Concerns\HasHashtags;


class Post extends Model
{
    use HasFactory, SoftDeletes, HasHashtags;

    protected $fillable = [
        'user_id',
        'body',
        'visibility',
        'is_edited',
        'is_pinned',
        'comments_enabled', 
        'shared_post_id',
        'group_id', 
        'status',

    ];

    protected $casts = [
        'is_edited' => 'boolean',
        'is_pinned' => 'boolean',
        'likes_count' => 'integer',
        'comments_enabled' => 'boolean', 
        'comments_count' => 'integer',
        'shares_count' => 'integer',
        'shares_count'     => 'integer',
    ];

    protected static function booted()
    {
        static::creating(function ($post) {
            $post->uuid = (string) Str::uuid();
        });
    }

        public function scopeFlagged($query)
    {
        return $query->where('is_flagged', true);
    }

        /**
     * Post body with #hashtags rendered as clickable links, HTML-escaped
     * everywhere else. Use this in views instead of raw $post->body.
     */
    public function bodyHtml(): string
    {
        return app(\App\Services\HashtagService::class)->linkify($this->body ?? '');
    }

    /**
     * The most common reaction type on this post — this is what everyone
     * sees as the icon next to the like count, regardless of their own
     * personal reaction. Don't confuse this with isReactedToBy(), which
     * returns the CURRENT VIEWER's own reaction and should only ever be
     * used to control the button's active/highlight state, never the icon.
     */
    public function topReactionType(): ?string
    {
        return $this->reactions()
            ->select('type')
            ->selectRaw('COUNT(*) as aggregate')
            ->groupBy('type')
            ->orderByDesc('aggregate')
            ->value('type');
    }

        /**
     * Breakdown of reaction types on this post, most common first.
     * Powers the small stacked emoji badges shown next to the like count
     * (e.g. ❤️👍🤩), NOT the action button — that button only ever
     * reflects the current viewer's own reaction (see isReactedToBy()).
     */
    public function reactionSummary()
    {
        return $this->reactions()
            ->select('type')
            ->selectRaw('COUNT(*) as aggregate')
            ->groupBy('type')
            ->orderByDesc('aggregate')
            ->get()
            ->map(fn ($row) => ['type' => $row->type, 'count' => $row->aggregate]);
    }

    /**
     * Top-level comments only (replies nested under each via ->replies()
     * on the Comment model) — loop over this when rendering comments.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id')->oldest();
    }

    /**
     * Every comment on this post regardless of nesting.
     */
    public function allComments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * The original post, if this post is itself a share/repost of another.
     */
    public function sharedPost()
    {
        return $this->belongsTo(Post::class, 'shared_post_id');
    }

    /**
     * Other posts that are shares/reposts of THIS post.
     */
    public function shares()
    {
        return $this->hasMany(Post::class, 'shared_post_id');
    }

    public function isSharedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->shares()->where('user_id', $user->id)->exists();
    }

    /**
     * All reactions on this post (👍/❤️/😆/😮/😢/😠) — same polymorphic
     * Reaction model used by comments, so a "reaction" is one concept
     * everywhere in the app, not a post-specific thing.
     */
    public function reactions()
    {
        return $this->morphMany(Reaction::class, 'reactable');
    }

    /**
     * What the given viewer reacted with, or null if they haven't reacted.
     */
    public function isReactedToBy(?User $user): ?string
    {
        if (!$user) {
            return null;
        }

        return $this->reactions()->where('user_id', $user->id)->value('type');
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

        public function images()
    {
        return $this->hasMany(PostImage::class)->orderBy('position');
    }

    /**
     * People specifically excluded from seeing this post.
     * Only meaningful when visibility = 'custom'.
     */
    public function excludedUsers()
    {
        return $this->belongsToMany(User::class, 'post_visibility_exceptions')
            ->withTimestamps();
    }


        public function videos()
        {
            return $this->hasMany(PostVideo::class)->orderBy('position');
        }

        // Handy scoped relations for later, once reels get their own feed/UI
        public function regularVideos()
        {
            return $this->videos()->where('type', 'video');
        }

        public function reels()
        {
            return $this->videos()->where('type', 'reel');
        }

        public function scopePublished($query)
        {
            return $query->where('status', 'published');
        }

        public function scopePendingIn($query, int $groupId)
        {
            return $query->where('group_id', $groupId)->where('status', 'pending');
        }

        public function group()
{
    return $this->belongsTo(Group::class);
}

    /**
     * Scope: only posts the given (authenticated) viewer is allowed to see.
     *
     * Rules:
     *  - The author can always see their own posts (including private ones).
     *  - "public" posts are visible to everyone, unless the viewer is
     *    specifically excluded.
     *  - "friends"/"custom" posts are visible only to accepted friends of
     *    the author, unless the viewer is specifically excluded.
     *  - "private" posts are never visible to anyone but the author.
     *
     * Assumes an authenticated viewer — wrap calls to this in your auth
     * middleware rather than passing a null user.
     */
    public function scopeVisibleTo($query, User $viewer)
    {
        return $query->where(function ($q) use ($viewer) {

            // 1. Your own posts, always
            $q->orWhere('user_id', $viewer->id);

            // 2. Public posts from others, minus exclusions
            $q->orWhere(function ($qq) use ($viewer) {
                $qq->where('visibility', 'public')
                    ->where('user_id', '!=', $viewer->id)
                    ->whereDoesntHave(
                        'excludedUsers',
                        fn ($e) => $e->where('users.id', $viewer->id)
                    );
            });

            // 3. Friends-only / custom posts from accepted friends, minus exclusions
            $q->orWhere(function ($qq) use ($viewer) {
                $qq->whereIn('visibility', ['friends', 'custom'])
                    ->where('user_id', '!=', $viewer->id)
                    ->whereExists(function ($sub) use ($viewer) {
                        $sub->select(DB::raw(1))
                            ->from('friendships')
                            ->where('status', 'accepted')
                            ->where(function ($f) use ($viewer) {
                                $f->where(function ($f1) use ($viewer) {
                                    $f1->where('requester_id', $viewer->id)
                                        ->whereColumn('addressee_id', 'posts.user_id');
                                })->orWhere(function ($f2) use ($viewer) {
                                    $f2->where('addressee_id', $viewer->id)
                                        ->whereColumn('requester_id', 'posts.user_id');
                                });
                            });
                    })
                    ->whereDoesntHave(
                        'excludedUsers',
                        fn ($e) => $e->where('users.id', $viewer->id)
                    );
            });
        });
    }
}