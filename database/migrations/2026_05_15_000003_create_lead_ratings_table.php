<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rater_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('quality');    // 1–5
            $table->unsignedTinyInteger('relevance');  // 1–5
            $table->unsignedTinyInteger('reactivity'); // 1–5
            $table->decimal('average_note', 3, 2)->storedAs('ROUND((quality + relevance + reactivity) / 3.0, 2)');
            $table->timestamp('rated_at')->useCurrent();
            $table->unique(['lead_id', 'rater_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_ratings');
    }
};
