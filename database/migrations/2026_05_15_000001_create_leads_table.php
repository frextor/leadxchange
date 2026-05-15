<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sender_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->foreignId('receiver_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->string('title', 150);
            $table->text('description')->nullable();

            $table->enum('category', [
                'web_dev', 'design', 'marketing', 'consulting',
                'finance', 'legal', 'hr', 'real_estate', 'other',
            ])->default('other');

            $table->decimal('budget', 10, 2)->nullable();

            $table->enum('status', ['new', 'accepted', 'rejected', 'converted'])
                  ->default('new');

            $table->timestamps();

            $table->index(['receiver_id', 'status']);
            $table->index(['sender_id',   'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
