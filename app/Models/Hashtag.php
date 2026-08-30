<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Hashtag extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'usage_count'];

    protected static function booted()
    {
        static::creating(function (Hashtag $hashtag) {
            $hashtag->uuid = (string) Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function posts()
    {
        return $this->morphedByMany(Post::class, 'hashtaggable');
    }

    public function scopeTrending($query, int $limit = 10)
    {
        return $query->orderByDesc('usage_count')->limit($limit);
    }
}