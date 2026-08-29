<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Sticker extends Model
{
    use HasFactory;

    protected $fillable = [
        'pack',
        'name',
        'image_path',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted()
    {
        static::creating(function (Sticker $sticker) {
            $sticker->uuid = (string) Str::uuid();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePack($query, string $pack)
    {
        return $query->where('pack', $pack);
    }
}