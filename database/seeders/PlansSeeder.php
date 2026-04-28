<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'basic',
                'price' => 0.00,
                'features' => [
                    'max_users' => 1,
                    'max_projects' => 3,
                    'storage_gb' => 5,
                    'support' => 'email',
                    'analytics' => false,
                    'custom_domain' => false,
                    'api_access' => false,
                    'priority_support' => false,
                    'advanced_reports' => false,
                    'team_collaboration' => false,
                    'video_calls' => false,
                    'integrations' => ['basic'],
                ],
            ],
            [
                'name' => 'ambassadeur',
                'price' => 29.99,
                'features' => [
                    'max_users' => 5,
                    'max_projects' => 15,
                    'storage_gb' => 50,
                    'support' => 'email_and_chat',
                    'analytics' => true,
                    'custom_domain' => true,
                    'api_access' => true,
                    'priority_support' => false,
                    'advanced_reports' => true,
                    'team_collaboration' => true,
                    'video_calls' => false,
                    'integrations' => ['basic', 'social_media', 'crm'],
                ],
            ],
            [
                'name' => 'premium_gold',
                'price' => 99.99,
                'features' => [
                    'max_users' => -1, // unlimited
                    'max_projects' => -1, // unlimited
                    'storage_gb' => 500,
                    'support' => '24_7_phone_and_chat',
                    'analytics' => true,
                    'custom_domain' => true,
                    'api_access' => true,
                    'priority_support' => true,
                    'advanced_reports' => true,
                    'team_collaboration' => true,
                    'video_calls' => true,
                    'integrations' => ['all'],
                    'white_label' => true,
                    'dedicated_account_manager' => true,
                    'custom_sla' => true,
                ],
            ],
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
}
