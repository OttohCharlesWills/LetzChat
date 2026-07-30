<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_videos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('post_id')->constrained()->cascadeOnDelete();

            // 'video' = a normal video attached to a regular post.
            // 'reel'  = short-form vertical video, different feed/UI later.
            // Kept on this same table (rather than a separate `reels` table)
            // since both are "a video attached to a post" at the storage
            // level — the type column is what lets the feed/query layer
            // treat them differently later without duplicating upload logic.
            $table->enum('type', ['video', 'reel'])->default('video');

            // Object key/path inside the Backblaze bucket, e.g.
            // "posts/videos/abc123.mp4" — NOT a public URL, since the
            // bucket is private. Used to generate a signed URL on demand.
            $table->string('path');

            // Original filename, useful for debugging/download naming.
            $table->string('original_name')->nullable();

            // Cached duration in seconds, if you extract it client-side
            // or via a follow-up job. Nullable since you may not have
            // this at upload time.
            $table->unsignedInteger('duration_seconds')->nullable();

            // Path to an auto-generated or client-generated thumbnail.
            // Nullable for now — can backfill later via a queued job.
            $table->string('thumbnail_path')->nullable();

            $table->unsignedInteger('size_bytes')->nullable();

            // Lets you preserve attachment order if a post ever holds
            // more than one video (mirrors post_images' `position`).
            $table->unsignedSmallInteger('position')->default(0);

            $table->timestamps();

            $table->index(['post_id', 'position']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_videos');
    }
};