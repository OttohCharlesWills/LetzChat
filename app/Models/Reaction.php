<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reactable_id',
        'reactable_type',
        'type',
    ];

    protected static function booted()
    {
        // Keep the reactable's likes_count in sync automatically. Works for
        // Post or Comment (or anything else reactable in the future) as long
        // as it has a likes_count column — no special-casing by type needed.
        static::created(function (Reaction $reaction) {
            $reaction->reactable()->increment('likes_count');
        });

        static::deleted(function (Reaction $reaction) {
            $reaction->reactable()->decrement('likes_count');
        });

        // Changing your reaction (like -> love) should NOT touch the count —
        // it's still exactly one reaction. Since Eloquent's update() doesn't
        // fire created/deleted, this is automatic; no extra code needed here.
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reactable()
    {
        return $this->morphTo();
    }

    /**
     * Human-friendly emoji for each reaction type, for convenience in views.
     */
    public static function emojiFor(string $type): string
    {
        return match ($type) {
            'like'  => '👍',
            'love'  => '❤️',
            'haha'  => '😆',
            'wow'   => '😮',
            'sad'   => '😢',
            'angry' => '😠',
            default => '👍',
        };
    }
}