<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            // Polymorphic target: works for posts now, comments later,
            // without needing a second reactions table or a schema change.
            $table->morphs('reactable'); // creates reactable_id + reactable_type + index

            // Facebook's six reactions
            $table->enum('type', ['like', 'love', 'haha', 'wow', 'sad', 'angry'])
                ->default('like');

            $table->timestamps();

            // One reaction per user per thing — changing your reaction
            // updates this row's `type`, it doesn't create a second row.
            $table->unique(['user_id', 'reactable_id', 'reactable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reactions');
    }
};