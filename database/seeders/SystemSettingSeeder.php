<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // Scoring window
            [
                'key'         => 'scoring.window_days',
                'value'       => '60',
                'type'        => 'int',
                'group'       => 'scoring',
                'description' => 'Rolling window in days for score computation',
            ],
            // Lead given/received multipliers
            [
                'key'         => 'scoring.given_multiplier',
                'value'       => '2',
                'type'        => 'int',
                'group'       => 'scoring',
                'description' => 'Points multiplier per lead sent and accepted',
            ],
            [
                'key'         => 'scoring.received_multiplier',
                'value'       => '-1',
                'type'        => 'int',
                'group'       => 'scoring',
                'description' => 'Points adjustment per lead received and accepted',
            ],
            // Lead type weights
            [
                'key'         => 'scoring.mql_weight',
                'value'       => '1',
                'type'        => 'int',
                'group'       => 'scoring',
                'description' => 'Bonus points for MQL (Marketing Qualified Lead) classification',
            ],
            [
                'key'         => 'scoring.sql_weight',
                'value'       => '3',
                'type'        => 'int',
                'group'       => 'scoring',
                'description' => 'Bonus points for SQL (Sales Qualified Lead) classification',
            ],
            [
                'key'         => 'scoring.sp_weight',
                'value'       => '5',
                'type'        => 'int',
                'group'       => 'scoring',
                'description' => 'Bonus points for SP (Sales Process) classification',
            ],
            // Points transactions
            [
                'key'         => 'points.lead_accepted_sender',
                'value'       => '2',
                'type'        => 'int',
                'group'       => 'points',
                'description' => 'Points credited to sender when receiver accepts a lead',
            ],
            [
                'key'         => 'points.lead_received_deduction',
                'value'       => '1',
                'type'        => 'int',
                'group'       => 'points',
                'description' => 'Points deducted from receiver when accepting a lead',
            ],
        ];

        foreach ($settings as $setting) {
            SystemSetting::updateOrCreate(['key' => $setting['key']], $setting);
        }
    }
}
