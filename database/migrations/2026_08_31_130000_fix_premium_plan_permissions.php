<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $premium = DB::table('plans')->where('name', 'premium')->first();

        if (! $premium) return;

        $perms = json_decode($premium->permissions ?? '{}', true) ?: [];

        // Premium ne peut pas créer de groupe ni d'événement —
        // ce sont des privilèges réservés aux Consuls et Ambassadeurs.
        $perms['can_create_pole']           = false;
        $perms['can_organize_group_events'] = false;
        $perms['can_organize_regional_events'] = false;

        DB::table('plans')
            ->where('name', 'premium')
            ->update(['permissions' => json_encode($perms)]);
    }

    public function down(): void
    {
        // On ne restaure pas les anciennes permissions ambiguës
    }
};
