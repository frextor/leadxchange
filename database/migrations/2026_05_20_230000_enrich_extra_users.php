<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // ── Create real companies ─────────────────────────────────────────────
        $companyNames = ['Microsoft', 'SAP', 'LinkedIn', 'Stripe', 'Notion', 'HubSpot', 'Salesforce', 'Oracle', 'Shopify', 'Google', 'Zendesk', 'Twilio', 'Adobe'];
        $companyIds   = [];
        foreach ($companyNames as $name) {
            $existing = DB::table('companies')->where('name', $name)->value('id');
            $companyIds[$name] = $existing ?? DB::table('companies')->insertGetId([
                'name'       => $name,
                'siret'      => str_pad(abs(crc32($name)), 14, '0', STR_PAD_LEFT),
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // ── Create interests if none exist ────────────────────────────────────
        $interestNames = ['Sales B2B', 'SaaS', 'Growth Hacking', 'Product Management', 'Marketing Digital', 'Tech & Innovation', 'Entrepreneuriat', 'CRM & Automation'];
        $interestIds   = [];
        foreach ($interestNames as $name) {
            $existing = DB::table('interests')->where('name', $name)->value('id');
            $interestIds[$name] = $existing ?? DB::table('interests')->insertGetId([
                'name'       => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // ── All users to enrich — looked up by email (no hardcoded IDs) ───────
        // city IDs: Casablanca=1, Paris=91, Dubai=74, Londres=62, Madrid=55, Amsterdam=53, Berlin=58, Bruxelles=51
        $allUsers = [
            // Extra users (created by seed_extra_users)
            ['email' => 'sofia.martinez@microsoft.com',  'company' => 'Microsoft',  'job_title' => 'Enterprise Account Manager', 'city_id' => 55,  'interests' => ['Sales B2B', 'CRM & Automation', 'SaaS']],
            ['email' => 'karim.idrissi@sap.com',         'company' => 'SAP',         'job_title' => 'Sales Director MENA',        'city_id' => 1,   'interests' => ['Sales B2B', 'SaaS', 'Entrepreneuriat']],
            ['email' => 'julie.bernard@linkedin.com',    'company' => 'LinkedIn',    'job_title' => 'Talent Acquisition Lead',    'city_id' => 91,  'interests' => ['Marketing Digital', 'Tech & Innovation', 'Product Management']],
            ['email' => 'omar.elfassi@stripe.com',       'company' => 'Stripe',      'job_title' => 'Partnerships Manager',       'city_id' => 62,  'interests' => ['SaaS', 'Tech & Innovation', 'Growth Hacking']],
            ['email' => 'lena.schneider@notion.so',      'company' => 'Notion',      'job_title' => 'Product Marketing Manager',  'city_id' => 58,  'interests' => ['Product Management', 'Marketing Digital', 'SaaS']],
            ['email' => 'mehdi.tazi@hubspot.ma',         'company' => 'HubSpot',     'job_title' => 'Growth Hacker',              'city_id' => 1,   'interests' => ['Growth Hacking', 'Marketing Digital', 'CRM & Automation']],
            ['email' => 'claire.fontaine@salesforce.fr', 'company' => 'Salesforce',  'job_title' => 'Customer Success Manager',   'city_id' => 91,  'interests' => ['CRM & Automation', 'Sales B2B', 'SaaS']],
            ['email' => 'youssef.amrani@oracle.ma',      'company' => 'Oracle',      'job_title' => 'Pre-Sales Engineer',         'city_id' => 1,   'interests' => ['Tech & Innovation', 'Sales B2B', 'SaaS']],
            ['email' => 'amelia.clarke@shopify.com',     'company' => 'Shopify',     'job_title' => 'Revenue Operations Lead',    'city_id' => 53,  'interests' => ['SaaS', 'Growth Hacking', 'Entrepreneuriat']],
            ['email' => 'amine.benkirane@google.com',    'company' => 'Google',      'job_title' => 'Strategic Partnerships',     'city_id' => 74,  'interests' => ['Tech & Innovation', 'Entrepreneuriat', 'Marketing Digital']],
            // Connected demo users (created by seed_demo_users)
            ['email' => 'antoine.moreau@salesforce.com', 'company' => 'Salesforce', 'job_title' => 'Sales Engineer',    'city_id' => 91, 'interests' => ['Sales B2B', 'CRM & Automation']],
            ['email' => 'nadia.benali@hubspot.com',      'company' => 'HubSpot',    'job_title' => 'Account Executive',  'city_id' => 91, 'interests' => ['Sales B2B', 'Growth Hacking']],
            ['email' => 'thomas.keller@zendesk.com',     'company' => 'Zendesk',    'job_title' => 'VP Sales EMEA',      'city_id' => 58, 'interests' => ['Sales B2B', 'SaaS']],
            ['email' => 'imane.rachidi@oracle.com',      'company' => 'Oracle',     'job_title' => 'Business Developer', 'city_id' => 1,  'interests' => ['Sales B2B', 'CRM & Automation']],
            ['email' => 'lucas.ferreira@twilio.com',     'company' => 'Twilio',     'job_title' => 'Head of Sales',      'city_id' => 55, 'interests' => ['SaaS', 'Tech & Innovation']],
            ['email' => 'camille.dupont@adobe.com',      'company' => 'Adobe',      'job_title' => 'Sales Manager',      'city_id' => 91, 'interests' => ['Marketing Digital', 'Sales B2B']],
        ];

        foreach ($allUsers as $data) {
            $uid = DB::table('users')->where('email', $data['email'])->value('id');
            if (!$uid) continue;

            DB::table('users')->where('id', $uid)->update([
                'city_id'    => $data['city_id'],
                'company_id' => $companyIds[$data['company']] ?? null,
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
