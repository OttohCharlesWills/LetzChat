<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    use HasFactory;

    protected $fillable = [
        'follower_id',
        'followed_id',
    ];

    protected static function booted()
    {
        // Keep users.followers_count / users.following_count in sync
        // automatically, so you never have to remember to update them
        // manually wherever follow/unfollow happens.
        static::created(function (Follow $follow) {
            User::whereKey($follow->followed_id)->increment('followers_count');
            User::whereKey($follow->follower_id)->increment('following_count');
        });

        static::deleted(function (Follow $follow) {
            User::whereKey($follow->followed_id)->decrement('followers_count');
            User::whereKey($follow->follower_id)->decrement('following_count');
        });
    }

    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    public function followed()
    {
        return $this->belongsTo(User::class, 'followed_id');
    }
}