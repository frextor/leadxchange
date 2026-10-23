<?php

namespace App\Console\Commands;

use App\Models\Plan;
use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Subscription;
use Illuminate\Console\Command;

class SyncGrantedPremium extends Command
{
    protected $signature = 'premium:sync-grants';

    protected $description = "Révoque les accès Premium offerts (essai/code promo) expirés, et accorde l'essai Full Access aux nouveaux éligibles";

    public function handle(): void
    {
        $this->revokeExpired();
        $this->grantTrialToEligible();
        $this->grantPromoToEligible();
    }

    /** Toute ligne source=trial|promo_code dont trial_ends_at est passé repasse en "canceled". */
    private function revokeExpired(): void
    {
        $expired = Subscription::with('user')
            ->where('status', 'active')
            ->whereIn('source', ['trial', 'promo_code'])
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<=', now())
            ->get();

        foreach ($expired as $sub) {
            $sub->update(['status' => 'canceled']);
            $sub->user?->revokePrivilegedRolesIfBasic();
        }

        $this->info("Révoqué {$expired->count()} accès Premium offert(s) expiré(s).");
    }

    /** Accorde (ou rattrape) l'essai Full Access aux comptes récents encore dans leur fenêtre. */
    private function grantTrialToEligible(): void
    {
        if (! SystemSetting::get('trial.enabled', false)) {
            return;
        }

        $months = (int) SystemSetting::get('trial.duration_months', 3);
        if ($months <= 0) {
            return;
        }

        $premiumPlan = Plan::where('name', 'premium')->first();
        if (! $premiumPlan) {
            $this->warn('Plan premium introuvable — essai non accordé.');
            return;
        }

        $granted = 0;

        User::where('role', 'user')
            ->where('created_at', '>=', now()->subMonths($months))
            ->where('ambassador_status', '!=', 'approved')
            ->where(fn ($q) => $q->whereNull('consul_status')->orWhere('consul_status', '!=', 'approved'))
            ->chunkById(200, function ($users) use ($premiumPlan, &$granted) {
                foreach ($users as $user) {
                    if (! $user->isOnFullAccessTrial()) continue;
                    if ($user->hasRealPlanAtLeast('premium')) continue;

                    Subscription::updateOrCreate(
                        ['user_id' => $user->id, 'source' => 'trial'],
                        [
                            'plan_id'       => $premiumPlan->id,
                            'status'        => 'active',
                            'trial_ends_at' => $user->fullAccessTrialEndsAt(),
                        ],
                    );
                    $granted++;
                }
            });

        $this->info("Essai Full Access synchronisé pour {$granted} compte(s).");
    }

    /**
     * Rattrape les comptes dont promo_premium_until est encore valide mais qui n'ont pas
     * (ou plus) de ligne subscription active correspondante — couvre les redemptions
     * faites avant l'introduction de cette synchronisation, et sert de filet de sécurité.
     */
    private function grantPromoToEligible(): void
    {
        $premiumPlan = Plan::where('name', 'premium')->first();
        if (! $premiumPlan) {
            $this->warn('Plan premium introuvable — code promo non synchronisé.');
            return;
        }

        $granted = 0;

        User::where('role', 'user')
            ->whereNotNull('promo_premium_until')
            ->where('promo_premium_until', '>', now())
            ->chunkById(200, function ($users) use ($premiumPlan, &$granted) {
                foreach ($users as $user) {
                    if ($user->hasRealPlanAtLeast('premium')) continue;

                    Subscription::updateOrCreate(
                        ['user_id' => $user->id, 'source' => 'promo_code'],
                        [
                            'plan_id'       => $premiumPlan->id,
                            'status'        => 'active',
                            'trial_ends_at' => $user->promo_premium_until,
                        ],
                    );
                    $granted++;
                }
            });

        $this->info("Code promo synchronisé pour {$granted} compte(s).");
    }
}
