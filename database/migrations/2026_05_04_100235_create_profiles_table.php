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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->unique();
            $table->string('avatar')->nullable();
            $table->text('bio')->nullable();
            $table->string('motto', 500)->nullable();
            $table->string('job_title')->nullable();
            $table->string('sector')->nullable();
            $table->enum('experience_level', ['junior', 'mid', 'senior', 'expert'])->nullable();
            $table->text('looking_for')->nullable();
            $table->text('services_offered')->nullable();
            $table->boolean('open_to_network')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
