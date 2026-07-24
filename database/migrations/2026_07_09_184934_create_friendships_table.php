<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('friendships', function (Blueprint $table) {
            $table->id();

            // The user who sent the friend request
            $table->foreignId('requester_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // The user who received the friend request
            $table->foreignId('addressee_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // pending  -> request sent, awaiting response
            // accepted -> both users are now friends
            // declined -> addressee rejected the request
            // blocked  -> addressee has blocked the requester
            $table->enum('status', ['pending', 'accepted', 'declined', 'blocked'])
                ->default('pending');

            $table->timestamp('accepted_at')->nullable();

            $table->timestamps();

            // Prevent duplicate requests between the same two users (in this direction)
            $table->unique(['requester_id', 'addressee_id']);

            // Speeds up "get all friendships involving this user" queries
            $table->index(['addressee_id', 'status']);
            $table->index(['requester_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('friendships');
    }
};