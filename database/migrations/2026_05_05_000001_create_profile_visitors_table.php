<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_visitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('visitor_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('visit_count')->default(1);
            $table->timestamp('last_visited_at');
            $table->boolean('is_new')->default(true);
            $table->timestamps();

            $table->unique(['profile_user_id', 'visitor_id']);
            $table->index(['profile_user_id', 'last_visited_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_visitors');
    }
};
