<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class BannedWord extends Model
{
    use HasFactory;

    public const CACHE_KEY = 'banned_words:list';

    protected $fillable = ['word', 'severity', 'category', 'added_by'];

    protected static function booted()
    {
        // Invalidate the cached list any time the table changes, so
        // ContentModerationService never checks against stale data.
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }
}