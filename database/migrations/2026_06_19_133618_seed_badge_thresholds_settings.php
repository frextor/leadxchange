<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            ['key' => 'badge_bronze_min',    'value' => '5',  'type' => 'int', 'group' => 'badges', 'description' => 'Score minimum pour le badge Bronze'],
            ['key' => 'badge_argent_min',    'value' => '10', 'type' => 'int', 'group' => 'badges', 'description' => 'Score minimum pour le badge Argent'],
            ['key' => 'badge_or_min',        'value' => '15', 'type' => 'int', 'group' => 'badges', 'description' => 'Score minimum pour le badge Or'],
            ['key' => 'badge_platinium_min', 'value' => '20', 'type' => 'int', 'group' => 'badges', 'description' => 'Score minimum pour le badge Platinium'],
        ];

        foreach ($settings as $s) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $s['key']],
                array_merge($s, ['created_at' => now(), 'updated_at' => now()])
            );
        }
    }

    public function down(): void
    {
        DB::table('system_settings')->whereIn('key', [
            'badge_bronze_min', 'badge_argent_min', 'badge_or_min', 'badge_platinium_min',
        ])->delete();
    }
};
