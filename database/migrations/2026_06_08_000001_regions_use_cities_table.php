<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // users.region_id : drop FK → regions, re-add FK → cities
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->foreign('region_id')->references('id')->on('cities')->nullOnDelete();
        });

        // events.region_id : drop FK → regions, re-add FK → cities
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->foreign('region_id')->references('id')->on('cities')->nullOnDelete();
        });

        // Drop the now-unused regions table
        Schema::dropIfExists('regions');
    }

    public function down(): void
    {
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('code', 10)->nullable();
            $table->string('country', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->foreign('region_id')->references('id')->on('regions')->nullOnDelete();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->foreign('region_id')->references('id')->on('regions')->nullOnDelete();
        });
    }
};
