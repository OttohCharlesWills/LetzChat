<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->id();

            // The user doing the following
            $table->foreignId('follower_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // The user being followed
            $table->foreignId('followed_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            // Can't follow the same person twice
            $table->unique(['follower_id', 'followed_id']);

            // Speeds up "who follows me" / "who do I follow" lookups
            $table->index('followed_id');
            $table->index('follower_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('follows');
    }
};