<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add initial_points to plans (configurable per plan)
        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedInteger('initial_points')->default(0)->after('max_groups');
        });

        DB::table('plans')->update(['initial_points' => 0]);

        // Add 'expired' to leads status enum
        DB::statement("ALTER TABLE leads MODIFY COLUMN status ENUM('new','accepted','rejected','converted','expired') NOT NULL DEFAULT 'new'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE leads MODIFY COLUMN status ENUM('new','accepted','rejected','converted') NOT NULL DEFAULT 'new'");

        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('initial_points');
        });
    }
};
