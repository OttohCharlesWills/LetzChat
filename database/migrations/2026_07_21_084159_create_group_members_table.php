<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_members', function (Blueprint $table) {
            $table->id();

            $table->foreignId('group_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // 'admin'     -> created the group (or promoted) — can edit
            //                settings, remove members, delete the group
            // 'moderator' -> can remove posts/members, can't delete the group
            // 'member'    -> can post and comment
            $table->enum('role', ['admin', 'moderator', 'member'])->default('member');

            $table->timestamp('joined_at')->useCurrent();

            $table->timestamps();

            $table->unique(['group_id', 'user_id']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_members');
    }
};