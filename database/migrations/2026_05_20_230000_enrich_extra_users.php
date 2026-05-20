<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // ── Create real companies ─────────────────────────────────────────────
        $companyNames = ['Microsoft', 'SAP', 'LinkedIn', 'Stripe', 'Notion', 'HubSpot', 'Salesforce', 'Oracle', 'Shopify', 'Google'];
        $companyIds   = [];
        foreach ($companyNames as $name) {
            $existing = DB::table('companies')->where('name', $name)->value('id');
            if ($existing) {
                $companyIds[$name] = $existing;
            } else {
                $companyIds[$name] = DB::table('companies')->insertGetId([
                    'name'       => $name,
                    'siret'      => '',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // ── Create interests if none exist ────────────────────────────────────
        $interestNames = ['Sales B2B', 'SaaS', 'Growth Hacking', 'Product Management', 'Marketing Digital', 'Tech & Innovation', 'Entrepreneuriat', 'CRM & Automation'];
        $interestIds   = [];
        foreach ($interestNames as $name) {
            $existing = DB::table('interests')->where('name', $name)->value('id');
            if ($existing) {
                $interestIds[$name] = $existing;
            } else {
                $interestIds[$name] = DB::table('interests')->insertGetId([
                    'name'       => $name,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        // ── User enrichment data ──────────────────────────────────────────────
        // city IDs: Casablanca=1, Paris=91, Dubai=74, Londres=62, Madrid=55, Amsterdam=53, Berlin=58, Bruxelles=51
        $users = [
            64 => ['email' => 'sofia.martinez@microsoft.com',  'company' => 'Microsoft',  'position' => 'Enterprise Account Manager', 'city_id' => 55,  'interests' => ['Sales B2B', 'CRM & Automation', 'SaaS']],
            65 => ['email' => 'karim.idrissi@sap.com',         'company' => 'SAP',         'position' => 'Sales Director MENA',        'city_id' => 1,   'interests' => ['Sales B2B', 'SaaS', 'Entrepreneuriat']],
            66 => ['email' => 'julie.bernard@linkedin.com',    'company' => 'LinkedIn',    'position' => 'Talent Acquisition Lead',    'city_id' => 91,  'interests' => ['Marketing Digital', 'Tech & Innovation', 'Product Management']],
            67 => ['email' => 'omar.elfassi@stripe.com',       'company' => 'Stripe',      'position' => 'Partnerships Manager',       'city_id' => 62,  'interests' => ['SaaS', 'Tech & Innovation', 'Growth Hacking']],
            68 => ['email' => 'lena.schneider@notion.so',      'company' => 'Notion',      'position' => 'Product Marketing Manager',  'city_id' => 58,  'interests' => ['Product Management', 'Marketing Digital', 'SaaS']],
            69 => ['email' => 'mehdi.tazi@hubspot.ma',         'company' => 'HubSpot',     'position' => 'Growth Hacker',              'city_id' => 1,   'interests' => ['Growth Hacking', 'Marketing Digital', 'CRM & Automation']],
            70 => ['email' => 'claire.fontaine@salesforce.fr', 'company' => 'Salesforce',  'position' => 'Customer Success Manager',   'city_id' => 91,  'interests' => ['CRM & Automation', 'Sales B2B', 'SaaS']],
            71 => ['email' => 'youssef.amrani@oracle.ma',      'company' => 'Oracle',      'position' => 'Pre-Sales Engineer',         'city_id' => 1,   'interests' => ['Tech & Innovation', 'Sales B2B', 'SaaS']],
            72 => ['email' => 'amelia.clarke@shopify.com',     'company' => 'Shopify',     'position' => 'Revenue Operations Lead',    'city_id' => 53,  'interests' => ['SaaS', 'Growth Hacking', 'Entrepreneuriat']],
            73 => ['email' => 'amine.benkirane@google.com',    'company' => 'Google',      'position' => 'Strategic Partnerships',     'city_id' => 74,  'interests' => ['Tech & Innovation', 'Entrepreneuriat', 'Marketing Digital']],
        ];

        foreach ($users as $userId => $data) {
            // Update user: city + position + company
            DB::table('users')->where('id', $userId)->update([
                'city_id'    => $data['city_id'],
                'position'   => $data['position'],
                'company_id' => $companyIds[$data['company']],
                'updated_at' => $now,
            ]);

            // Assign interests (skip duplicates)
            foreach ($data['interests'] as $interestName) {
                $interestId = $interestIds[$interestName];
                $exists = DB::table('user_interests')
                    ->where('user_id', $userId)
                    ->where('interest_id', $interestId)
                    ->exists();
                if (!$exists) {
                    DB::table('user_interests')->insert([
                        'user_id'     => $userId,
                        'interest_id' => $interestId,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ]);
                }
            }
        }

        // Also enrich the 6 connected demo users (IDs depend on seed order — look up by email)
        $connectedUsers = [
            ['email' => 'antoine.moreau@salesforce.com',  'company' => 'Salesforce', 'position' => 'Sales Engineer',    'city_id' => 91, 'interests' => ['Sales B2B', 'CRM & Automation']],
            ['email' => 'nadia.benali@hubspot.com',       'company' => 'HubSpot',    'position' => 'Account Executive',  'city_id' => 91, 'interests' => ['Sales B2B', 'Growth Hacking']],
            ['email' => 'thomas.keller@zendesk.com',      'company' => null,         'position' => 'VP Sales EMEA',      'city_id' => 58, 'interests' => ['Sales B2B', 'SaaS']],
            ['email' => 'imane.rachidi@oracle.com',       'company' => 'Oracle',     'position' => 'Business Developer', 'city_id' => 1,  'interests' => ['Sales B2B', 'CRM & Automation']],
            ['email' => 'lucas.ferreira@twilio.com',      'company' => null,         'position' => 'Head of Sales',      'city_id' => 55, 'interests' => ['SaaS', 'Tech & Innovation']],
            ['email' => 'camille.dupont@adobe.com',       'company' => null,         'position' => 'Sales Manager',      'city_id' => 91, 'interests' => ['Marketing Digital', 'Sales B2B']],
        ];

        // Create Zendesk, Twilio, Adobe companies if needed
        foreach (['Zendesk', 'Twilio', 'Adobe'] as $name) {
            $existing = DB::table('companies')->where('name', $name)->value('id');
            if (!$existing) {
                $companyIds[$name] = DB::table('companies')->insertGetId([
                    'name' => $name, 'siret' => '', 'created_at' => $now, 'updated_at' => $now,
                ]);
            } else {
                $companyIds[$name] = $existing;
            }
        }

        foreach ($connectedUsers as $data) {
            $uid = DB::table('users')->where('email', $data['email'])->value('id');
            if (!$uid) continue;

            DB::table('users')->where('id', $uid)->update([
                'city_id'    => $data['city_id'],
                'position'   => $data['position'],
                'company_id' => $data['company'] ? $companyIds[$data['company']] : null,
                'updated_at' => $now,
            ]);

            foreach ($data['interests'] as $interestName) {
                $interestId = $interestIds[$interestName];
                $exists = DB::table('user_interests')
                    ->where('user_id', $uid)
                    ->where('interest_id', $interestId)
                    ->exists();
                if (!$exists) {
                    DB::table('user_interests')->insert([
                        'user_id'     => $uid,
                        'interest_id' => $interestId,
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void {}
};
