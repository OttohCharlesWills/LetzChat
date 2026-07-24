<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Device Details
            $table->string('device_name');           // e.g. Samsung S24
            $table->enum('device_type', [
                'mobile',
                'desktop',
                'tablet',
                'web'
            ]);         // mobile, desktop, tablet
            $table->enum('operating_system', [
                'Android',
                'iOS',
                'Windows',
                'macOS',
                'Linux'
            ]);
            $table->string('os_version')->nullable();

            // Browser (for website logins)
            $table->string('browser')->nullable();
            $table->string('browser_version')->nullable();

            // Identification
            $table->string('device_identifier')->nullable();
            $table->string('push_token')->nullable();

            // Security
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            // Trust & Activity
            $table->boolean('is_trusted')->default(false);
            $table->boolean('is_current')->default(false);

            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_active_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};