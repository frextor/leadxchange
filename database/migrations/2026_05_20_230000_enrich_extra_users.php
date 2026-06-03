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
                    'siret'      => str_pad(abs(crc32($name)), 14, '0', STR_PAD_LEFT),
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
            64 => ['email' => 'sofia.martinez@microsoft.com',  'company' => 'Microsoft',  'job_title' => 'Enterprise Account Manager', 'city_id' => 55,  'interests' => ['Sales B2B', 'CRM & Automation', 'SaaS']],
            65 => ['email' => 'karim.idrissi@sap.com',         'company' => 'SAP',         'job_title' => 'Sales Director MENA',        'city_id' => 1,   'interests' => ['Sales B2B', 'SaaS', 'Entrepreneuriat']],
            66 => ['email' => 'julie.bernard@linkedin.com',    'company' => 'LinkedIn',    'job_title' => 'Talent Acquisition Lead',    'city_id' => 91,  'interests' => ['Marketing Digital', 'Tech & Innovation', 'Product Management']],
            67 => ['email' => 'omar.elfassi@stripe.com',       'company' => 'Stripe',      'job_title' => 'Partnerships Manager',       'city_id' => 62,  'interests' => ['SaaS', 'Tech & Innovation', 'Growth Hacking']],
            68 => ['email' => 'lena.schneider@notion.so',      'company' => 'Notion',      'job_title' => 'Product Marketing Manager',  'city_id' => 58,  'interests' => ['Product Management', 'Marketing Digital', 'SaaS']],
            69 => ['email' => 'mehdi.tazi@hubspot.ma',         'company' => 'HubSpot',     'job_title' => 'Growth Hacker',              'city_id' => 1,   'interests' => ['Growth Hacking', 'Marketing Digital', 'CRM & Automation']],
            70 => ['email' => 'claire.fontaine@salesforce.fr', 'company' => 'Salesforce',  'job_title' => 'Customer Success Manager',   'city_id' => 91,  'interests' => ['CRM & Automation', 'Sales B2B', 'SaaS']],
            71 => ['email' => 'youssef.amrani@oracle.ma',      'company' => 'Oracle',      'job_title' => 'Pre-Sales Engineer',         'city_id' => 1,   'interests' => ['Tech & Innovation', 'Sales B2B', 'SaaS']],
            72 => ['email' => 'amelia.clarke@shopify.com',     'company' => 'Shopify',     'job_title' => 'Revenue Operations Lead',    'city_id' => 53,  'interests' => ['SaaS', 'Growth Hacking', 'Entrepreneuriat']],
            73 => ['email' => 'amine.benkirane@google.com',    'company' => 'Google',      'job_title' => 'Strategic Partnerships',     'city_id' => 74,  'interests' => ['Tech & Innovation', 'Entrepreneuriat', 'Marketing Digital']],
        ];

        foreach ($users as $userId => $data) {
            // Update user: city + company
            DB::table('users')->where('id', $userId)->update([
                'city_id'    => $data['city_id'],
                'company_id' => $companyIds[$data['company']],
                'updated_at' => $now,
            ]);
            DB::table('profiles')->updateOrInsert(
                ['user_id' => $userId],
                ['job_title' => $data['job_title'], 'updated_at' => $now, 'created_at' => $now]
            );

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
            ['email' => 'antoine.moreau@salesforce.com',  'company' => 'Salesforce', 'job_title' => 'Sales Engineer',    'city_id' => 91, 'interests' => ['Sales B2B', 'CRM & Automation']],
            ['email' => 'nadia.benali@hubspot.com',       'company' => 'HubSpot',    'job_title' => 'Account Executive',  'city_id' => 91, 'interests' => ['Sales B2B', 'Growth Hacking']],
            ['email' => 'thomas.keller@zendesk.com',      'company' => null,         'job_title' => 'VP Sales EMEA',      'city_id' => 58, 'interests' => ['Sales B2B', 'SaaS']],
            ['email' => 'imane.rachidi@oracle.com',       'company' => 'Oracle',     'job_title' => 'Business Developer', 'city_id' => 1,  'interests' => ['Sales B2B', 'CRM & Automation']],
            ['email' => 'lucas.ferreira@twilio.com',      'company' => null,         'job_title' => 'Head of Sales',      'city_id' => 55, 'interests' => ['SaaS', 'Tech & Innovation']],
            ['email' => 'camille.dupont@adobe.com',       'company' => null,         'job_title' => 'Sales Manager',      'city_id' => 91, 'interests' => ['Marketing Digital', 'Sales B2B']],
        ];

        // Create Zendesk, Twilio, Adobe companies if needed
        foreach (['Zendesk', 'Twilio', 'Adobe'] as $name) {
            $existing = DB::table('companies')->where('name', $name)->value('id');
            if (!$existing) {
                $companyIds[$name] = DB::table('companies')->insertGetId([
                    'name' => $name, 'siret' => str_pad(abs(crc32($name)), 14, '0', STR_PAD_LEFT), 'created_at' => $now, 'updated_at' => $now,
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
                'company_id' => $data['company'] ? $companyIds[$data['company']] : null,
                'updated_at' => $now,
            ]);
            DB::table('profiles')->updateOrInsert(
                ['user_id' => $uid],
                ['job_title' => $data['job_title'], 'updated_at' => $now, 'created_at' => $now]
            );

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
