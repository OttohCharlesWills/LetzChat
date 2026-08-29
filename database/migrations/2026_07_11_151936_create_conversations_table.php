<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('group_id')
                ->nullable()
                ->unique()
                ->after('type')
                ->constrained('groups')
                ->cascadeOnDelete();

            // 'private' = exactly 2 participants (a normal 1:1 DM)
            // 'group'   = 3+ participants, has a name/avatar of its own
            $table->enum('type', ['private', 'group'])->default('public');

            // Only used for group chats
            $table->string('name')->nullable();
            $table->string('avatar')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Denormalized pointer to the latest message, so the chat list
            // (Messenger flyout, /messages page) can sort & preview without
            // a heavy join/subquery on every page load.
            $table->foreignId('last_message_id')->nullable();

            $table->timestamp('last_message_at')->nullable();

            $table->timestamps();

            $table->index(['type', 'last_message_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};