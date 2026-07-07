<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ambassador_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->text('biography')->nullable();
            $table->string('linkedin_url', 255)->nullable();
            $table->integer('score')->default(0);
            $table->integer('national_rank')->nullable();
            $table->integer('regional_rank')->nullable();
            $table->json('achievements')->nullable();   // ['bronze','silver',...]
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ambassador_profiles');
    }
};
