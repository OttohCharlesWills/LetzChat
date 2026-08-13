<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            // 'everyone'   -> any member can post
            // 'admin_only' -> only admin/moderator can post
            $table->enum('post_permission', ['everyone', 'admin_only'])
                  ->default('everyone')
                  ->after('privacy');

            // If true AND post_permission is 'everyone', member posts go
            // into a pending queue instead of publishing immediately.
            // Has no effect when post_permission is 'admin_only', since
            // only admin/moderator can post at all in that case, and
            // their own posts never need approval.
            $table->boolean('require_post_approval')
                  ->default(false)
                  ->after('post_permission');
        });
    }

    public function down(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn(['post_permission', 'require_post_approval']);
        });
    }
};