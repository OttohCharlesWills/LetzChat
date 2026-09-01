<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // advertiser
            $table->foreignId('post_id')->constrained()->cascadeOnDelete(); // the boosted post

            // draft     -> created but not yet running (not used yet, reserved for scheduling later)
            // active    -> currently eligible to be shown
            // paused    -> advertiser paused it manually
            // completed -> budget exhausted or end_date passed
            $table->enum('status', ['draft', 'active', 'paused', 'completed'])->default('active');

            $table->decimal('budget', 12, 2);       // total prepaid budget, escrowed on creation
            $table->decimal('spent', 12, 2)->default(0);
            $table->decimal('cost_per_impression', 8, 4)->default(0.5); // flat CPM-style cost, NGN per impression

            $table->date('start_date');
            $table->date('end_date');

            // Targeting — all nullable = "no restriction on this dimension"
            $table->unsignedTinyInteger('target_min_age')->nullable();
            $table->unsignedTinyInteger('target_max_age')->nullable();
            $table->enum('target_gender', ['any', 'male', 'female'])->default('any');
            $table->json('target_locations')->nullable(); // array of location strings, matched loosely

            $table->unsignedInteger('impressions_count')->default(0);
            $table->unsignedInteger('clicks_count')->default(0);

            $table->timestamps();

            $table->index(['status', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};