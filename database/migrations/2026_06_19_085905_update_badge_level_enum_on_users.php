<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY badge_level ENUM('neutre','bronze','argent','or','platinium') NOT NULL DEFAULT 'neutre'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY badge_level ENUM('bronze','argent','or') NOT NULL DEFAULT 'bronze'");
    }
};
