<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * La colonne system_settings.type a pour défaut 'int' (voir migration
 * 2026_06_10_000002). SystemSetting::set() ne précisait jamais de type
 * explicitement, donc toute valeur JSON/texte enregistrée via cette méthode
 * se retrouvait avec type='int' — et était donc réduite à (int) 0 à la
 * lecture via SystemSetting::get(). Corrige les clés JSON/texte connues
 * déjà enregistrées avec le mauvais type.
 */
return new class extends Migration
{
    private const AFFECTED_KEYS = [
        'admin_nav_superadmin',
        'admin_nav_plateforme',
        'nav_menu_config',
        'geo_blocked_cities',
        'geo_blocked_countries',
    ];

    public function up(): void
    {
        DB::table('system_settings')
            ->whereIn('key', self::AFFECTED_KEYS)
            ->where('type', '!=', 'string')
            ->update(['type' => 'string']);
    }

    public function down(): void
    {
        // Pas de retour en arrière pertinent : remettre type='int' recréerait le bug.
    }
};
