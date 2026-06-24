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
            'platinium' => SystemSetting::get('badge_platinium_min', 20),
            'or'        => SystemSetting::get('badge_or_min',        15),
            'argent'    => SystemSetting::get('badge_argent_min',    10),
            'bronze'    => SystemSetting::get('badge_bronze_min',     5),
            'neutre'    => 0,
        ];
    }

    public function calculate(User $user): int
    {
        $since = now()->subDays(self::WINDOW_DAYS);

        // Leads envoyés dans la fenêtre (par type)
        $sent = DB::table('leads')
            ->where('sender_id', $user->id)
            ->where('created_at', '>=', $since)
            ->select('lead_type', DB::raw('count(*) as cnt'))
            ->groupBy('lead_type')
            ->pluck('cnt', 'lead_type');

        $totalSent = $sent->sum();

        // Points base envoi + qualification
        $sentPoints = $totalSent * self::SENT_BONUS;
        foreach (self::TYPE_WEIGHTS as $type => $weight) {
            $sentPoints += ($sent[$type] ?? 0) * $weight;
        }

        // Leads reçus dans la fenêtre
        $receivedCount = DB::table('leads')
            ->where('receiver_id', $user->id)
            ->where('created_at', '>=', $since)
            ->count();

        $receivedPoints = $receivedCount * self::RECEIVED_MALUS;

        return max(0, $sentPoints + $receivedPoints);
    }

    public function badge(int $score): string
    {
        foreach (self::thresholds() as $badge => $threshold) {
            if ($score >= $threshold) {
                return $badge;
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
