<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        $password = Hash::make('Demo@1234');
        $now      = now();

        $extras = [
            ['first_name' => 'Sofia',    'last_name' => 'Martinez',  'email' => 'sofia.martinez@microsoft.com',   'job_title' => 'Enterprise Account Manager', 'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=200&q=80'],
            ['first_name' => 'Karim',    'last_name' => 'Idrissi',   'email' => 'karim.idrissi@sap.com',          'job_title' => 'Sales Director MENA',        'avatar' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=200&q=80'],
            ['first_name' => 'Julie',    'last_name' => 'Bernard',   'email' => 'julie.bernard@linkedin.com',     'job_title' => 'Talent Acquisition Lead',    'avatar' => 'https://images.unsplash.com/photo-1531746020798-e6953c6e8e04?w=200&q=80'],
            ['first_name' => 'Omar',     'last_name' => 'El Fassi',  'email' => 'omar.elfassi@stripe.com',        'job_title' => 'Partnerships Manager',       'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=200&q=80'],
            ['first_name' => 'Lena',     'last_name' => 'Schneider', 'email' => 'lena.schneider@notion.so',       'job_title' => 'Product Marketing Manager',  'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=200&q=80'],
            ['first_name' => 'Mehdi',    'last_name' => 'Tazi',      'email' => 'mehdi.tazi@hubspot.ma',          'job_title' => 'Growth Hacker',              'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=200&q=80'],
            ['first_name' => 'Claire',   'last_name' => 'Fontaine',  'email' => 'claire.fontaine@salesforce.fr',  'job_title' => 'Customer Success Manager',   'avatar' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=200&q=80'],
            ['first_name' => 'Youssef',  'last_name' => 'Amrani',    'email' => 'youssef.amrani@oracle.ma',       'job_title' => 'Pre-Sales Engineer',         'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=200&q=80'],
            ['first_name' => 'Amelia',   'last_name' => 'Clarke',    'email' => 'amelia.clarke@shopify.com',      'job_title' => 'Revenue Operations Lead',    'avatar' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=200&q=80'],
            ['first_name' => 'Amine',    'last_name' => 'Benkirane', 'email' => 'amine.benkirane@google.com',     'job_title' => 'Strategic Partnerships',     'avatar' => 'https://images.unsplash.com/photo-1519345182560-3f2917c472ef?w=200&q=80'],
        ];

        foreach ($extras as $user) {
            $userId = DB::table('users')->insertGetId([
                'first_name'           => $user['first_name'],
                'last_name'            => $user['last_name'],
                'email'                => $user['email'],
                'password'             => $password,
                'gender'               => 'male',
                'onboarding_completed' => true,
                'points_balance'       => 0,
                'badge_level'          => 'bronze',
                'created_at'           => $now,
                'updated_at'           => $now,
            ]);

            DB::table('profiles')->insert([
                'user_id'         => $userId,
                'job_title'       => $user['job_title'],
                'avatar'          => $user['avatar'],
                'open_to_network' => true,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
        }
    }

    public function down(): void {}
};
