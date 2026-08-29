<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stickers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            // Groups stickers into sets, e.g. 'default', 'emotions', 'animals'
            $table->string('pack')->default('default');

            $table->string('name');
            $table->string('image_path');

            $table->unsignedInteger('sort_order')->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->index(['pack', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stickers');
    }
};