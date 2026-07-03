<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('enterprise_licenses', function (Blueprint $table) {
            $table->id();
            // The company account that "holds" the pack
            $table->foreignId('holder_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans');
            // Total seats purchased in the contract
            $table->unsignedSmallInteger('seats_total')->default(1);
            // Denormalised counter — kept in sync with enterprise_invitations count
            $table->unsignedSmallInteger('seats_used')->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enterprise_licenses');
    }
};
