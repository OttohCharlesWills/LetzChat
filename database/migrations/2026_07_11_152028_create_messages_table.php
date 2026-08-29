<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
 
            $table->foreignId('conversation_id')
                ->constrained()
                ->cascadeOnDelete();
 
            $table->foreignId('sender_id')
                ->constrained('users')
                ->cascadeOnDelete();
 
            // 'text'   -> body has the message text
            // 'image'  -> attachment_path has the image, body optional caption
            // 'file'   -> attachment_path has the file, attachment_name for display
            // 'voice'  -> attachment_path has the audio recording, duration_seconds set
            // 'system' -> body holds text like "X added Y to the group" (no sender bubble)
            $table->enum('type', ['text', 'image', 'file', 'voice', 'sticker', 'system'])->default('text');
 
            $table->text('body')->nullable();
 
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->unsignedBigInteger('attachment_size')->nullable();
 
            // Voice note playback length, in seconds — only set when type = 'voice'
            $table->unsignedInteger('duration_seconds')->nullable();
 
            // Reply/quote support
            $table->foreignId('reply_to_id')
                ->nullable()
                ->constrained('messages')
                ->nullOnDelete();
 
            $table->timestamp('edited_at')->nullable();
 
            // "Unsend" — soft delete keeps the row (so unread counts / ordering
            // stay consistent) but the UI shows "You unsent a message" like FB.
            $table->softDeletes();
 
            $table->timestamps();
 
            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};