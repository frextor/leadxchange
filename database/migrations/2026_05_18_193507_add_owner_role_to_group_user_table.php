<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE group_user MODIFY role ENUM('member', 'admin', 'owner') NOT NULL DEFAULT 'member'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE group_user MODIFY role ENUM('member', 'admin') NOT NULL DEFAULT 'member'");
    }
};
