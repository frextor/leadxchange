<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now  = now();
        $rows = [
            [
                'key'         => 'currency_symbol',
                'value'       => '€',
                'type'        => 'string',
                'group'       => 'currency',
                'description' => 'Symbole ou code devise affiché (ex: €, MAD, $, £)',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'currency_position',
                'value'       => 'after',
                'type'        => 'string',
                'group'       => 'currency',
                'description' => 'Position du symbole : before | after',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'currency_decimals',
                'value'       => '0',
                'type'        => 'int',
                'group'       => 'currency',
                'description' => 'Nombre de décimales (0 ou 2)',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'currency_thousands_sep',
                'value'       => ' ',
                'type'        => 'string',
                'group'       => 'currency',
                'description' => 'Séparateur de milliers (espace, virgule, point)',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'key'         => 'currency_decimal_sep',
                'value'       => '.',
                'type'        => 'string',
                'group'       => 'currency',
                'description' => 'Séparateur décimal (point ou virgule)',
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ];

        foreach ($rows as $row) {
            DB::table('system_settings')->insertOrIgnore($row);
        }
    }

    public function down(): void
    {
        DB::table('system_settings')
            ->where('group', 'currency')
            ->delete();
    }
};
