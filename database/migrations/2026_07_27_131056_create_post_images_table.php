<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('post_images', function (Blueprint $table) {
            $table->id();

            $table->foreignId('post_id')->constrained()->cascadeOnDelete();

            // Full Cloudinary URL, e.g.
            // https://res.cloudinary.com/dbau8vqu0/image/upload/v.../abc123.jpg
            $table->string('url');

            // Cloudinary public_id — save this too, not just the url.
            // You'll need it later if you ever want to delete the image
            // from Cloudinary itself (Cloudinary delete calls use
            // public_id, not the url).
            $table->string('public_id')->nullable();

            // Lets you preserve the order the user attached images in,
            // and makes a "cover image" / first-image lookup trivial.
            $table->unsignedSmallInteger('position')->default(0);

            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();

            $table->timestamps();

            $table->index(['post_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_images');
    }
};