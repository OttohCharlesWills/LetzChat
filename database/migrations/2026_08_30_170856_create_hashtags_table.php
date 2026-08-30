<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hashtags', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->string('name')->unique(); // stored lowercase, without the '#'
            $table->unsignedInteger('usage_count')->default(0);

            $table->timestamps();

            $table->index('usage_count');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hashtags');
    }
};