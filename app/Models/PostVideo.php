<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PostVideo extends Model
{
    protected $fillable = [
        'post_id', 'type', 'path', 'original_name',
        'duration_seconds', 'thumbnail_path', 'size_bytes', 'position',
    ];

    /**
     * Generate a temporary signed URL for playback, since the
     * Backblaze bucket is private. Default expiry: 1 hour.
     */
    public function url(int $expiresInMinutes = 60): string
    {
        return Storage::disk('backblaze')->temporaryUrl(
            $this->path,
            now()->addMinutes($expiresInMinutes)
        );
    }

    public function thumbnailUrl(int $expiresInMinutes = 60): ?string
    {
        if (! $this->thumbnail_path) {
            return null;
        }

        return Storage::disk('backblaze')->temporaryUrl(
            $this->thumbnail_path,
            now()->addMinutes($expiresInMinutes)
        );
    }
}