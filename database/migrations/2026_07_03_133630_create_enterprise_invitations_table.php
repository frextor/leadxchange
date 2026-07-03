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
        Schema::create('enterprise_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('license_id')->constrained('enterprise_licenses')->cascadeOnDelete();
            // Who sent the invite (always the holder for now)
            $table->foreignId('invited_by')->constrained('users');
            $table->string('email');
            // Filled once the user accepts (or the auto-created account is linked)
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'active', 'revoked'])->default('pending');
            // Unique token for the join link
            $table->string('token', 64)->unique();
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            // Prevent duplicate invitations for the same email on the same license
            $table->unique(['license_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enterprise_invitations');
    }
};
