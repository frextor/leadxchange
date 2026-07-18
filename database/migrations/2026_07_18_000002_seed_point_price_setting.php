<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('system_settings')->updateOrInsert(
            ['key' => 'payments.point_price_cents'],
            [
                'key'         => 'payments.point_price_cents',
                'value'       => '100',
                'type'        => 'int',
                'group'       => 'payments',
                'description' => 'Price per point in cents (100 = 1.00€)',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('system_settings')->where('key', 'payments.point_price_cents')->delete();
    }
};
