<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversation_participants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conversation_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // For group chats later: 'member' vs 'admin' (can rename/add people, etc.)
            $table->enum('role', ['member', 'admin'])->default('member');

            // Everything with created_at <= last_read_at is "read".
            // Powers unread badges without a separate per-message read table.
            $table->timestamp('last_read_at')->nullable();

            // Lets a user "leave" a group chat, or "delete" a private conversation
            // for themselves only, without affecting the other participant(s).
            $table->timestamp('left_at')->nullable();

            // Mute notifications for this conversation without leaving it
            $table->boolean('is_muted')->default(false);

            $table->timestamps();

            $table->unique(['conversation_id', 'user_id']);
            $table->index(['user_id', 'last_read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversation_participants');
    }
};