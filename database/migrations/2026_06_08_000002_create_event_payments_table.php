<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_payment_intent_id')->nullable()->index();
            $table->unsignedInteger('amount');
            $table->string('currency', 3)->default('eur');
            $table->string('status')->default('requires_payment_method')->index();
            $table->text('failure_message')->nullable();
            $table->timestamps();

            $table->unique('stripe_payment_intent_id');
            $table->index(['user_id', 'event_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_payments');
    }
};
