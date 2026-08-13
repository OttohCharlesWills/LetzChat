<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            // 'published' -> visible as normal (default for all non-group
            //                posts, and group posts that don't need approval)
            // 'pending'   -> awaiting admin/moderator review, hidden from feed
            // Rejected posts are simply deleted per your spec, so no
            // 'rejected' state needs to persist.
            $table->enum('status', ['published', 'pending'])
                  ->default('published')
                  ->after('visibility');

            $table->index(['group_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};