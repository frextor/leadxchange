<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('points_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_session_id')->unique();
            $table->string('stripe_payment_intent_id')->nullable();
            $table->unsignedInteger('points_purchased');
            $table->unsignedInteger('amount_cents');
            $table->string('currency', 3)->default('eur');
            $table->enum('source', ['free_purchase', 'negative_balance_topup'])->default('free_purchase');
            $table->enum('status', ['succeeded', 'failed'])->default('succeeded');
            $table->timestamps();

            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('points_payments');
    }
};
