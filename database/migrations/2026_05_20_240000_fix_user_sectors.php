<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // ── Clean up wrongly created interests / user_interests ───────────────
        $wrongInterestIds = DB::table('interests')
            ->whereIn('name', ['Sales B2B', 'SaaS', 'Growth Hacking', 'Product Management', 'Marketing Digital', 'Tech & Innovation', 'Entrepreneuriat', 'CRM & Automation'])
            ->pluck('id');

        if ($wrongInterestIds->isNotEmpty()) {
            DB::table('user_interests')->whereIn('interest_id', $wrongInterestIds)->delete();
            DB::table('interests')->whereIn('id', $wrongInterestIds)->delete();
        }

        // ── Sectors: 3=Consulting, 4=Design, 5=E-commerce, 7=Finance,
        //             10=IA, 13=Marketing, 15=RH, 17=Startup,
        //             18=Technologie, 20=Ventes ────────────────────────────────

        // ── French cities: Paris=91, Lyon=93, Marseille=92, Toulouse=94,
        //    Bordeaux=99, Lille=100, Nantes=96, Strasbourg=98,
        //    Montpellier=97, Nice=95, Rennes=101 ─────────────────────────────

        $extraUsers = [
            //  id   city  sector_ids       looking_for   services_offered
            64 => ['city' => 91,  'sector_ids' => [20, 18, 3],  'looking_for' => [18, 3],    'services' => [20, 3]],   // Sofia  — Paris
            65 => ['city' => 93,  'sector_ids' => [20, 3, 17],  'looking_for' => [3, 17],    'services' => [20, 3]],   // Karim  — Lyon
            66 => ['city' => 91,  'sector_ids' => [15, 13, 3],  'looking_for' => [3, 13],    'services' => [15, 13]],  // Julie  — Paris
            67 => ['city' => 99,  'sector_ids' => [7, 18, 17],  'looking_for' => [17, 18],   'services' => [7, 3]],    // Omar   — Bordeaux
            68 => ['city' => 98,  'sector_ids' => [13, 18, 17], 'looking_for' => [18, 17],   'services' => [13, 3]],   // Lena   — Strasbourg
            69 => ['city' => 92,  'sector_ids' => [13, 20, 5],  'looking_for' => [20, 5],    'services' => [13, 20]],  // Mehdi  — Marseille
            70 => ['city' => 91,  'sector_ids' => [20, 3, 18],  'looking_for' => [20, 3],    'services' => [3, 18]],   // Claire — Paris
            71 => ['city' => 93,  'sector_ids' => [18, 20, 3],  'looking_for' => [20, 3],    'services' => [18, 3]],   // Youssef— Lyon
            72 => ['city' => 96,  'sector_ids' => [5, 20, 17],  'looking_for' => [17, 5],    'services' => [20, 5]],   // Amelia — Nantes
            73 => ['city' => 91,  'sector_ids' => [18, 10, 17], 'looking_for' => [17, 10],   'services' => [18, 10]],  // Amine  — Paris
        ];

        $connectedUsers = [
            'antoine.moreau@salesforce.com'  => ['city' => 91,  'sector_ids' => [20, 3, 18], 'looking_for' => [20, 3],  'services' => [20, 3]],   // Paris
            'nadia.benali@hubspot.com'        => ['city' => 94,  'sector_ids' => [20, 13, 3], 'looking_for' => [13, 20], 'services' => [20, 13]],  // Toulouse
            'thomas.keller@zendesk.com'       => ['city' => 100, 'sector_ids' => [20, 18, 3], 'looking_for' => [3, 18],  'services' => [20, 3]],   // Lille
            'imane.rachidi@oracle.com'        => ['city' => 92,  'sector_ids' => [20, 3, 17], 'looking_for' => [3, 17],  'services' => [20, 3]],   // Marseille
            'lucas.ferreira@twilio.com'       => ['city' => 99,  'sector_ids' => [18, 20, 17],'looking_for' => [17, 18], 'services' => [20, 18]],  // Bordeaux
            'camille.dupont@adobe.com'        => ['city' => 91,  'sector_ids' => [13, 20, 4], 'looking_for' => [13, 20], 'services' => [13, 4]],   // Paris
        ];

        foreach ($extraUsers as $userId => $data) {
            DB::table('users')->where('id', $userId)->update([
                'city_id'    => $data['city'],
                'updated_at' => $now,
            ]);
            DB::table('profiles')->where('user_id', $userId)->update([
                'sector_ids'       => json_encode($data['sector_ids']),
                'looking_for'      => json_encode($data['looking_for']),
                'services_offered' => json_encode($data['services']),
                'updated_at'       => $now,
            ]);
        }

        foreach ($connectedUsers as $email => $data) {
            $uid = DB::table('users')->where('email', $email)->value('id');
            if (!$uid) continue;
            DB::table('users')->where('id', $uid)->update([
                'city_id'    => $data['city'],
                'updated_at' => $now,
            ]);
            DB::table('profiles')->where('user_id', $uid)->update([
                'sector_ids'       => json_encode($data['sector_ids']),
                'looking_for'      => json_encode($data['looking_for']),
                'services_offered' => json_encode($data['services']),
                'updated_at'       => $now,
            ]);
        }
    }

    public function down(): void {}
};
