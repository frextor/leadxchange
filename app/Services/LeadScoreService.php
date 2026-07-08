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

    // Fenêtre glissante en jours — TEST: réduit à 1h (rollback: remettre subDays(self::WINDOW_DAYS) dans calculate())
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
        $since = now()->subHours(1); // TEST (rollback: subDays(self::WINDOW_DAYS))

        // Base : tous les leads acceptés → sender +2, receiver -1
        $acceptedSentCount = DB::table('leads')
            ->where('sender_id', $user->id)
            ->whereIn('status', ['accepted', 'converted'])
            ->where('accepted_at', '>=', $since)
            ->count();

        $basePoints = $acceptedSentCount * self::SENT_BONUS;

        // Bonus type : leads notés dans les 15 jours → +MQL/SQL/SP
        $ratedSent = DB::table('leads')
            ->join('lead_ratings', 'lead_ratings.lead_id', '=', 'leads.id')
            ->where('leads.sender_id', $user->id)
            ->whereIn('leads.status', ['accepted', 'converted'])
            ->where('leads.accepted_at', '>=', $since)
            ->whereColumn('lead_ratings.rated_at', '<=', 'leads.rating_due_at')
            ->select('leads.lead_type', DB::raw('count(*) as cnt'))
            ->groupBy('leads.lead_type')
            ->pluck('cnt', 'leads.lead_type');

        $bonusPoints = 0;
        foreach (self::TYPE_WEIGHTS as $type => $weight) {
            $bonusPoints += ($ratedSent[$type] ?? 0) * $weight;
        }

        $sentPoints = $basePoints + $bonusPoints;

        // Leads reçus et acceptés → -1 chacun
        $receivedCount = DB::table('leads')
            ->where('receiver_id', $user->id)
            ->whereIn('status', ['accepted', 'converted'])
            ->where('accepted_at', '>=', $since)
            ->count();

        $receivedPoints = $receivedCount * self::RECEIVED_MALUS;

        // Malus type sur les leads reçus et notés → MQL:-1, SQL:-3, SP:-5
        $ratedReceived = DB::table('leads')
            ->join('lead_ratings', 'lead_ratings.lead_id', '=', 'leads.id')
            ->where('leads.receiver_id', $user->id)
            ->whereIn('leads.status', ['accepted', 'converted'])
            ->where('leads.accepted_at', '>=', $since)
            ->whereColumn('lead_ratings.rated_at', '<=', 'leads.rating_due_at')
            ->select('leads.lead_type', DB::raw('count(*) as cnt'))
            ->groupBy('leads.lead_type')
            ->pluck('cnt', 'leads.lead_type');

        $receivedBonusDeduction = 0;
        foreach (self::TYPE_WEIGHTS as $type => $weight) {
            $receivedBonusDeduction -= ($ratedReceived[$type] ?? 0) * $weight;
        }

        return $sentPoints + $receivedPoints + $receivedBonusDeduction;
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
            'points_balance' => $score,
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
