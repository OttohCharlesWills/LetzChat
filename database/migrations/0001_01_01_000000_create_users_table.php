<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
 
            // Personal Information
            $table->string('first_name');
            $table->string('last_name');
            $table->string('username')->unique();
 
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();
 
            // Contact
            $table->string('email')->unique();
            $table->string('phone')->nullable()->unique();
 
            // Profile
            $table->text('bio')->nullable();
            $table->string('location')->nullable();
            $table->string('education')->nullable();
            $table->string('avatar')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('cover_photo')->nullable();
            $table->boolean('profile_completed')->default(false);
 
            // Occupation: JSON so it can hold one or many entries, e.g.
            // [{"title": "Software Engineer", "company": "Acme Inc", "current": true}, ...]
            $table->json('occupation')->nullable();
 
            // Contact info: JSON so it can hold an arbitrary set of links/handles, e.g.
            // {"instagram": "@sonia", "tiktok": "@so_nia4177", "website": "https://..."}
            $table->json('contact_info')->nullable();
 
            // Follower counts — denormalized counters only. These do NOT track
            // who follows whom; that would require a separate `follows` table
            // (same pattern as your `friendships` table) if/when you build
            // real follow/unfollow functionality.
            $table->unsignedInteger('followers_count')->default(0);
            $table->unsignedInteger('following_count')->default(0);
 
            // Authentication
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
 
            $table->string('password');
 
            // Account Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_banned')->default(false);
 
            // Presence
            $table->boolean('is_online')->default(false);
            $table->timestamp('last_seen_at')->nullable();
 
            // Settings
            $table->string('timezone')->default('Africa/Lagos');
            $table->string('language')->default('en');
 
            $table->rememberToken();
 
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
