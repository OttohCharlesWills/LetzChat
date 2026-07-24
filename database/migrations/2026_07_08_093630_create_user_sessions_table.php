<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('device_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Session ID
            $table->uuid('session_uuid')->unique();

            // Entry & Exit
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();

            $table->enum('platform', [
                'web',
                'android',
                'ios',
                'desktop'
            ]);

            // Current Status
            $table->boolean('is_active')->default(true);

            // Duration
            $table->unsignedInteger('duration_seconds')->default(0);

            // Navigation
            $table->string('entry_screen')->nullable(); // login, home, chat
            $table->string('exit_screen')->nullable();

            // Network
            $table->string('ip_address', 45)->nullable();

            // Location (optional)
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();

            // Client Info
            $table->text('user_agent')->nullable();

            // Future-proof
            $table->json('metadata')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
