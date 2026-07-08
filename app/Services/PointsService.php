<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PointsService — implements the CGU Section 6 points system.
 *
 * Points map (CGU §6.2.2):
 *   UQ / null  →  0 pts bonus
 *   MQL        →  1 pt  bonus
 *   SQL        →  3 pts bonus
 *   SP         →  5 pts bonus
 *
 * Constants (CGU §6.2 & §6.3):
 *   Send credit     : +2 pts  (sender, on acceptance)
 *   Receive debit   : -1 pt   (receiver, on acceptance)
 *   Cap             :  30 pts max
 *
 * Expired lead revert (CGU §6.2.3):
 *   Sender loses initial +2 pts → -2
 *   Receiver recovers -1 pt    → +1
 *
 * Malus (CGU §6.2.3):
 *   > 3 UQ leads over 6 rolling months → -5 pts (applied once per period)
 */
class PointsService
{
    const SEND_CREDIT    = 2;
    const RECEIVE_DEBIT  = -1;
    const CAP            = 30;
    const MIN_TO_RECEIVE = 1;   // must have ≥ 1 pt to receive

    const BONUS_MAP = [
        'MQL' => 1,
        'SQL' => 3,
        'SP'  => 5,
    ];

    const MALUS_UQ_THRESHOLD  = 3;   // > 3 UQ leads → malus
    const MALUS_WINDOW_MONTHS = 6;
    const MALUS_AMOUNT        = -5;
    const RATING_DAYS         = 15; // TEST: deadline posée à J+15 mais vérification ci-dessous changée à +1h
    const EXTENSION_DAYS      = 15;

    // ── Core adjustment (respects 30-pt cap, writes points_history, updates badge) ──
    public function adjust(User $user, int $delta, string $reason = ''): void
    {
        if ($delta === 0) return;

        DB::transaction(function () use ($user, $delta, $reason) {
            $user->refresh();
            $from = (int) ($user->points_balance ?? 0);
            $user->adjustPoints($delta, $reason);

            Log::info('Points adjustment', [
                'user_id' => $user->id,
                'delta'   => $delta,
                'from'    => $from,
                'to'      => (int) ($user->fresh()->points_balance ?? 0),
                'reason'  => $reason,
            ]);
        });
    }

    // ── Lead sent + accepted → credit sender +2, debit receiver -1 ───────────
    public function onLeadAccepted(Lead $lead): void
    {
        if ($lead->sender_points_credited) return;

        DB::transaction(function () use ($lead) {
            $this->adjust($lead->sender, +self::SEND_CREDIT, 'lead_sent_accepted');
            $this->adjust($lead->receiver, self::RECEIVE_DEBIT, 'lead_received');

            $lead->update([
                'sender_points_credited' => true,
                'accepted_at'   => now(),
                'rating_due_at' => now()->addDays(self::RATING_DAYS),
            ]);
        });
    }

    // ── Receiver rates the lead → debit receiver -N, credit sender +N ────────
    public function onLeadRated(Lead $lead, string $leadType): void
    {
        $bonus = self::BONUS_MAP[$leadType] ?? 0;

        DB::transaction(function () use ($lead, $bonus, $leadType) {
            $lead->update([
                'bonus_points'  => $bonus,
                'rated_bonus_at'=> now(),
            ]);

            if ($bonus > 0) {
                $this->adjust($lead->receiver, -$bonus, "bonus_given_{$leadType}");
                $this->adjust($lead->sender,   +$bonus, "bonus_received_{$leadType}");
            }
        });
    }

    // ── Extend rating window by 15 more days (one time) ──────────────────────
    public function extendRatingDeadline(Lead $lead): void
    {
        if ($lead->rating_extended_at) {
            throw new \RuntimeException('Délai déjà prolongé une fois.');
        }

        $newDeadline = ($lead->rating_due_at ?? now())->addDays(self::EXTENSION_DAYS);
        $lead->update([
            'rating_due_at'      => $newDeadline,
            'rating_extended_at' => now(),
        ]);
    }

    // ── Expired lead: revert points, check malus ──────────────────────────────
    public function processExpiredLead(Lead $lead): void
    {
        if ($lead->expiry_processed) return;

        DB::transaction(function () use ($lead) {
            if ($lead->sender_points_credited) {
                // Revert sender's +2 (only if user still exists)
                if ($lead->sender) $this->adjust($lead->sender, -self::SEND_CREDIT, 'lead_expired_revert');
                // Revert receiver's -1
                if ($lead->receiver) $this->adjust($lead->receiver, +abs(self::RECEIVE_DEBIT), 'lead_expired_revert');
            }

            $lead->update([
                'expiry_processed' => true,
                'bonus_points'     => 0,
                'lead_type'        => null,
            ]);

            // Check malus on sender (CGU §6.2.3)
            if ($lead->sender) $this->checkMalus($lead->sender);
        });
    }

    // ── Malus: >3 UQ leads in 6 months → -5 pts ──────────────────────────────
    private function checkMalus(User $sender): void
    {
        $since = now()->subMonths(self::MALUS_WINDOW_MONTHS);

        $uqCount = Lead::where('sender_id', $sender->id)
            ->where('expiry_processed', true)
            ->where('bonus_points', 0)
            ->where('updated_at', '>=', $since)
            ->count();

        if ($uqCount > self::MALUS_UQ_THRESHOLD) {
            $this->adjust($sender, self::MALUS_AMOUNT, 'malus_uq_excess');

            Log::warning('Malus applied', [
                'user_id'  => $sender->id,
                'uq_count' => $uqCount,
                'malus'    => self::MALUS_AMOUNT,
            ]);
        }
    }

    // ── Check if user can receive leads (balance ≥ 1) ─────────────────────────
    public function canReceive(User $user): bool
    {
        return (int)($user->points_balance ?? 0) >= self::MIN_TO_RECEIVE;
    }

    // ── Check if user can send leads (balance ≥ 0) ────────────────────────────
    public function canSend(User $user): bool
    {
        if (!SystemSetting::get('leads.block_negative_sender', true)) {
            return true;
        }
        return (int)($user->points_balance ?? 0) >= 0;
    }

    // ── Bonus points for a given lead type ───────────────────────────────────
    public static function bonusFor(?string $leadType): int
    {
        return self::BONUS_MAP[$leadType] ?? 0;
    }
}
