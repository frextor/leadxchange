<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['looking_for', 'services_offered']);
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->json('looking_for')->nullable()->after('experience_level');
            $table->json('services_offered')->nullable()->after('looking_for');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['looking_for', 'services_offered']);
        });

        Schema::table('profiles', function (Blueprint $table) {
            $table->text('looking_for')->nullable()->after('experience_level');
            $table->text('services_offered')->nullable()->after('looking_for');
        });
    }
};
