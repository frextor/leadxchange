<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Consul et Ambassadeur ne sont pas des plans d'abonnement classiques —
        // ce sont des statuts accordés aux membres Premium. On les masque de la page upgrade.
        DB::table('plans')
            ->whereIn('name', ['consul', 'ambassadeur'])
            ->update(['is_visible' => false]);
    }

    public function down(): void
    {
        DB::table('plans')
            ->whereIn('name', ['consul', 'ambassadeur'])
            ->update(['is_visible' => true]);
    }
};
