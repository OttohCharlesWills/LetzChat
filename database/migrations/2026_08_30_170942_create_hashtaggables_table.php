<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hashtaggables', function (Blueprint $table) {
            $table->id();

            $table->foreignId('hashtag_id')->constrained()->cascadeOnDelete();

            $table->unsignedBigInteger('hashtaggable_id');
            $table->string('hashtaggable_type');

            $table->timestamps();

            $table->unique(['hashtag_id', 'hashtaggable_id', 'hashtaggable_type'], 'hashtaggable_unique');
            $table->index(['hashtaggable_id', 'hashtaggable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hashtaggables');
    }
};