<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'  => 'basic',
                'price' => 0.00,
                'features' => [
                    '10 leads per month',
                    'View public profiles',
                    'Community access',
                    'Basic member search',
                    'Join up to 3 groups',
                    'Email support',
                ],
            ],
            [
                'name'  => 'ambassadeur',
                'price' => 29.99,
                'features' => [
                    'Unlimited leads',
                    'Full profile access',
                    'AI-written intro messages',
                    'Priority inbox',
                    'Advanced search & filters',
                    'Join unlimited groups',
                    'Analytics dashboard',
                    'Export contacts (CSV)',
                    'Email & chat support',
                ],
            ],
            [
                'name'  => 'premium_gold',
                'price' => 99.99,
                'features' => [
                    'Everything in Ambassadeur',
                    'Verified profile badge',
                    'Featured in search results',
                    'Co-host & create events',
                    'CRM integrations',
                    'White-label lead exports',
                    'Dedicated account manager',
                    '24/7 priority support',
                ],
            ],
        ];

        foreach ($plans as $data) {
            Plan::where('name', $data['name'])->update([
                'features' => json_encode($data['features']),
                'price'    => $data['price'],
            ]);
        }
    }
}
