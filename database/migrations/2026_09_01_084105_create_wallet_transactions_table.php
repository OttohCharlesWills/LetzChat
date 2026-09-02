<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();

            // 'topup'  -> money added (placeholder for real payment gateway)
            // 'spend'  -> budget escrowed when an ad is created
            // 'refund' -> unspent budget returned when an ad ends early/completes
            $table->enum('type', ['topup', 'spend', 'refund']);

            $table->decimal('amount', 12, 2);
            $table->decimal('balance_after', 12, 2);

            $table->foreignId('ad_id')->nullable()->constrained()->nullOnDelete();

            $table->unique('reference')->nullable(); // for a future payment gateway's transaction ref
            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};