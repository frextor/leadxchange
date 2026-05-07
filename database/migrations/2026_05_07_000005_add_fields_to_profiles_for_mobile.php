<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('website')->nullable()->after('sector');
            $table->string('region', 100)->nullable()->after('website');
            $table->string('linkedin')->nullable()->after('region');
            $table->json('sector_ids')->nullable()->after('linkedin');
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn(['website', 'region', 'linkedin', 'sector_ids']);
        });
    }
};
