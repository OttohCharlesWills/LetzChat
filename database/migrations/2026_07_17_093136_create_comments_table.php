<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('post_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Null = top-level comment on the post.
            // Set = this is a reply to another comment (same table, self-referencing).
            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('comments')
                ->cascadeOnDelete();

            $table->text('body');

            $table->boolean('is_edited')->default(false);

            // Cached count so the reaction picker/heart count doesn't need a
            // live COUNT() query on every comment render — same pattern as
            // posts.likes_count, kept in sync by the Reaction model's hooks.
            $table->unsignedInteger('likes_count')->default(0);

            // Soft delete: a deleted comment can render as "This comment was
            // deleted" while any replies underneath it stay intact — same
            // reasoning as messages' "unsend" behavior.
            $table->softDeletes();

            $table->timestamps();

            $table->index(['post_id', 'created_at']);
            $table->index(['parent_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};