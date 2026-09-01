<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            // Nullable, no FK constraint here on purpose: this is the base
            // posts migration and `groups` doesn't exist yet at this point
            // in the migration order (it's created later). The actual
            // foreign key constraint gets added in a separate migration
            // once the groups table exists — see
            // 2026_07_21_000003_add_group_id_to_posts_table.php.
            $table->unsignedBigInteger('group_id')->nullable();
            $table->index(['group_id', 'created_at']);

            // Self-reference for shares/reposts. Placed right after id()
            // instead of using ->after('id') — that clause only works on
            // Schema::table() (ALTER TABLE), not Schema::create().
            $table->foreignId('shared_post_id')->nullable()->constrained('posts')->nullOnDelete();

            $table->uuid('uuid')->unique();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('views_count')->default(0);

            // Text content for now. You'll add an `image` (or `image_path`)
            // column later — keep it nullable since a post could eventually
            // be image-only with no body text.
            $table->text('body')->nullable();

            // public    = anyone
            // friends   = accepted friends only
            // custom    = public/friends, except people listed in
            //             post_visibility_exceptions
            // private   = only the author
            $table->enum('visibility', ['public', 'friends', 'custom', 'private'])
                  ->default('public');

            $table->boolean('is_edited')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->boolean('comments_enabled')->default(true);

            $table->unsignedInteger('likes_count')->default(0);
            $table->unsignedInteger('comments_count')->default(0);
            $table->unsignedInteger('shares_count')->default(0);

            // 'published' -> visible as normal (default for all non-group
            //                posts, and group posts that don't need approval)
            // 'pending'   -> awaiting admin/moderator review, hidden from feed
            // Rejected posts are simply deleted per your spec, so no
            // 'rejected' state needs to persist.
            $table->enum('status', ['published', 'pending'])->default('published');

            // Flagged posts stay visible (flag = review, block = reject
            // outright at creation) but get tagged for the admin queue.
            $table->boolean('is_flagged')->default(false);
            $table->json('flagged_words')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'created_at']);
            $table->index(['group_id', 'status']);
            $table->index('is_flagged');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};