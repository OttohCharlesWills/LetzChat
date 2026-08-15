<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->string('name');
            $table->text('description')->nullable();

            // 'public'  -> anyone can see posts and join instantly
            // 'private' -> membership required to see posts;
            //              joining can be automatic or require admin approval
            $table->enum('privacy', ['public', 'private'])->default('public');

            // 'automatic' -> joining adds the user immediately
            // 'manual'    -> joining creates a pending request for approval
            $table->enum('join_approval', ['automatic', 'manual'])
                ->default('automatic');

            $table->string('cover_photo')->nullable();

            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnDelete();

            // Denormalized counter, kept in sync by GroupMember's model hooks
            $table->unsignedInteger('members_count')->default(0);

            $table->timestamps();

            $table->index('privacy');
            $table->index('join_approval');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};