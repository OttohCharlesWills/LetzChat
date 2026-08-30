<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banned_words', function (Blueprint $table) {
            $table->id();

            $table->string('word')->unique();

            // 'flag'  -> content is saved but marked for admin review, still visible
            // 'block' -> content is rejected outright, never saved
            $table->enum('severity', ['flag', 'block'])->default('flag');

            $table->string('category')->nullable(); // e.g. 'hate', 'violence', 'spam'

            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index('severity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banned_words');
    }
};