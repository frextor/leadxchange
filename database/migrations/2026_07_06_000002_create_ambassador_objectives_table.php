<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ambassador_objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ambassador_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('month');   // 1–12
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('target_members')->default(30);
            $table->unsignedInteger('target_events')->default(5);
            $table->unsignedInteger('target_leads')->default(100);
            $table->unique(['ambassador_id', 'month', 'year']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ambassador_objectives');
    }
};
