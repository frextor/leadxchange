<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // S'assurer que le plan Entreprise est bien visible sur la page upgrade.
        DB::table('plans')
            ->where('name', 'enterprise')
            ->update(['is_visible' => true, 'is_active' => true]);
    }

    public function down(): void {}
};
