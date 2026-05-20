<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        // Keep only user 52 (Anass Alouane — main account)
        $keepId = 52;

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // Clean up everything tied to users we're removing
        $removeIds = DB::table('users')->where('id', '!=', $keepId)->pluck('id');

        DB::table('connections')->whereIn('sender_id', $removeIds)->orWhereIn('receiver_id', $removeIds)->delete();
        DB::table('group_user')->whereIn('user_id', $removeIds)->delete();
        DB::table('group_invitations')->whereIn('user_id', $removeIds)->delete();
        DB::table('profiles')->whereIn('user_id', $removeIds)->delete();
        DB::table('device_tokens')->whereIn('user_id', $removeIds)->delete();

        // Delete posts and comments by removed users
        $postIds = DB::table('posts')->whereIn('user_id', $removeIds)->pluck('id');
        DB::table('comments')->whereIn('post_id', $postIds)->delete();
        DB::table('posts')->whereIn('user_id', $removeIds)->delete();
        DB::table('comments')->whereIn('user_id', $removeIds)->delete();

        DB::table('users')->where('id', '!=', $keepId)->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        // Demo users
        $demos = [
            ['first_name' => 'Antoine',  'last_name' => 'Moreau',   'email' => 'antoine.moreau@salesforce.com',  'job_title' => 'Sales Engineer',    'company' => 'Salesforce', 'avatar' => 'https://images.unsplash.com/photo-1463453091185-61582044d556?w=200&q=80'],
            ['first_name' => 'Nadia',    'last_name' => 'Benali',   'email' => 'nadia.benali@hubspot.com',        'job_title' => 'Account Executive',  'company' => 'HubSpot',    'avatar' => 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?w=200&q=80'],
            ['first_name' => 'Thomas',   'last_name' => 'Keller',   'email' => 'thomas.keller@zendesk.com',       'job_title' => 'VP Sales EMEA',       'company' => 'Zendesk',    'avatar' => 'https://images.unsplash.com/photo-1560250097-0b93528c311a?w=200&q=80'],
            ['first_name' => 'Imane',    'last_name' => 'Rachidi',  'email' => 'imane.rachidi@oracle.com',        'job_title' => 'Business Developer', 'company' => 'Oracle',     'avatar' => 'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?w=200&q=80'],
            ['first_name' => 'Lucas',    'last_name' => 'Ferreira', 'email' => 'lucas.ferreira@twilio.com',       'job_title' => 'Head of Sales',      'company' => 'Twilio',     'avatar' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=200&q=80'],
            ['first_name' => 'Camille',  'last_name' => 'Dupont',   'email' => 'camille.dupont@adobe.com',        'job_title' => 'Sales Manager',      'company' => 'Adobe',      'avatar' => 'https://images.unsplash.com/photo-1580489944761-15a19d654956?w=200&q=80'],
        ];

        $password = Hash::make('Demo@1234');
        $now      = now();

        foreach ($demos as $demo) {
            $userId = DB::table('users')->insertGetId([
                'first_name'             => $demo['first_name'],
                'last_name'              => $demo['last_name'],
                'email'                  => $demo['email'],
                'password'               => $password,
                'gender'                 => 'male',
                'onboarding_completed'   => true,
                'points_balance'         => 0,
                'badge_level'            => 'bronze',
                'created_at'             => $now,
                'updated_at'             => $now,
            ]);

            DB::table('profiles')->insert([
                'user_id'          => $userId,
                'job_title'        => $demo['job_title'],
                'avatar'           => $demo['avatar'],
                'open_to_network'  => true,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);

            // Connect each demo user to user 52 (accepted)
            DB::table('connections')->insert([
                'sender_id'   => $keepId,
                'receiver_id' => $userId,
                'status'      => 'accepted',
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }

    public function down(): void {}
};
