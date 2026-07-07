<?php

namespace App\Services;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LeadScoreService
{
    // Poids par type de lead envoyé
    const TYPE_WEIGHTS = [
        'MQL' => 1,
        'SQL' => 3,
        'SP'  => 5,
    ];

    // Bonus de base par lead envoyé
    const SENT_BONUS     = 2;
    // Malus par lead reçu
    const RECEIVED_MALUS = -1;

    // Fenêtre glissante en jours
    const WINDOW_DAYS = 60;

    // Seuils des badges (fallback si pas en DB)
    const BADGE_THRESHOLDS_DEFAULT = [
        'platinium' => 20,
        'or'        => 15,
        'argent'    => 10,
        'bronze'    => 5,
        'neutre'    => 0,
    ];

    public static function thresholds(): array
    {
        return [
            'platinium' => (int) SystemSetting::get('badge_platinium_min', 20),
            'or'        => (int) SystemSetting::get('badge_or_min',        15),
            'argent'    => (int) SystemSetting::get('badge_argent_min',    10),
            'bronze'    => (int) SystemSetting::get('badge_bronze_min',     5),
            'neutre'    => 0,
        ];
    }

    /**
     * Returns explicit [min, max] ranges per badge (max=null means ∞).
     * Falls back to derived values when explicit max not yet stored.
     */
    public static function ranges(): array
    {
        $t = static::thresholds();

        return [
            'neutre'    => [
                'min' => 0,
                'max' => (int) SystemSetting::get('badge_neutre_max',   $t['bronze']    - 1),
            ],
            'bronze'    => [
                'min' => $t['bronze'],
                'max' => (int) SystemSetting::get('badge_bronze_max',   $t['argent']    - 1),
            ],
            'argent'    => [
                'min' => $t['argent'],
                'max' => (int) SystemSetting::get('badge_argent_max',   $t['or']        - 1),
            ],
            'or'        => [
                'min' => $t['or'],
                'max' => (int) SystemSetting::get('badge_or_max',       $t['platinium'] - 1),
            ],
            'platinium' => [
                'min' => $t['platinium'],
                'max' => null,
            ],
        ];
    }

    public function calculate(User $user): int
    {
        $since = now()->subDays(self::WINDOW_DAYS);

        // Leads envoyés, acceptés ET notés dans les 15 jours après acceptation
        $sent = DB::table('leads')
            ->join('lead_ratings', 'lead_ratings.lead_id', '=', 'leads.id')
            ->where('leads.sender_id', $user->id)
            ->whereIn('leads.status', ['accepted', 'converted'])
            ->where('leads.accepted_at', '>=', $since)
            ->whereColumn('lead_ratings.rated_at', '<=', 'leads.rating_due_at')
            ->select('leads.lead_type', DB::raw('count(*) as cnt'))
            ->groupBy('leads.lead_type')
            ->pluck('cnt', 'leads.lead_type');

        $totalSent = $sent->sum();

        // Points base envoi + qualification
        $sentPoints = $totalSent * self::SENT_BONUS;
        foreach (self::TYPE_WEIGHTS as $type => $weight) {
            $sentPoints += ($sent[$type] ?? 0) * $weight;
        }

        // Leads reçus, acceptés ET notés dans les 15 jours
        $receivedCount = DB::table('leads')
            ->join('lead_ratings', 'lead_ratings.lead_id', '=', 'leads.id')
            ->where('leads.receiver_id', $user->id)
            ->whereIn('leads.status', ['accepted', 'converted'])
            ->where('leads.accepted_at', '>=', $since)
            ->whereColumn('lead_ratings.rated_at', '<=', 'leads.rating_due_at')
            ->count();

        $receivedPoints = $receivedCount * self::RECEIVED_MALUS;

        return $sentPoints + $receivedPoints;
    }

    public function badge(int $score): string
    {
        // Iterate from highest badge to lowest; first range that contains the score wins.
        foreach (array_reverse(self::ranges()) as $badgeName => $range) {
            if ($score >= $range['min'] && ($range['max'] === null || $score <= $range['max'])) {
                return $badgeName;
            }
        }
        return 'neutre';
    }

    public function updateUser(User $user): void
    {
        $score = $this->calculate($user);
        $badge = $this->badge($score);

        $user->update([
            'points_balance' => max(0, $score),
            'badge_level'    => $badge,
        ]);
    }

    public function updateAll(): int
    {
        $count = 0;
        User::where('role', 'user')->chunk(100, function ($users) use (&$count) {
            foreach ($users as $user) {
                $this->updateUser($user);
                $count++;
            }
        });
        return $count;
    }
}
