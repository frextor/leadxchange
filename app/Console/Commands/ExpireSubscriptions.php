<?php

namespace App\Console\Commands;

use App\Mail\SystemNotificationMail;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class ExpireSubscriptions extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'Mark subscriptions as canceled when cancel_at_period_end date has passed';

    public function handle(): void
    {
        $testMode = (bool) \App\Models\SystemSetting::get('payments.subscription_test_mode');

        $query = Subscription::with('plan', 'user')
            ->where('status', 'active')
            ->whereNotNull('current_period_end')
            ->where('current_period_end', '<', Carbon::now());

        if (!$testMode) {
            // Production: only expire subscriptions the user explicitly cancelled
            $query->where('cancel_at_period_end', true);
        }

        $subscriptions = $query->get();

        foreach ($subscriptions as $subscription) {
            // In test mode, simulate a successful renewal for auto-renewing subscriptions
            if ($testMode && !$subscription->cancel_at_period_end) {
                $key = $subscription->billing_period === 'annual'
                    ? 'payments.subscription_test_annual_minutes'
                    : 'payments.subscription_test_monthly_minutes';
                $minutes = (int) (\App\Models\SystemSetting::get($key) ?: 5);

                $subscription->update([
                    'current_period_end' => Carbon::now()->addMinutes($minutes),
                ]);
                $this->notifyRenewal($subscription);
                continue;
            }

            $subscription->update(['status' => 'canceled']);
            $subscription->user->revokePrivilegedRolesIfBasic();
        }

        $count = $subscriptions->count();
        $this->info("Expired {$count} subscription(s)." . ($testMode ? ' [TEST MODE]' : ''));
    }

    private function notifyRenewal(Subscription $subscription): void
    {
        $user = $subscription->user;
        $plan = $subscription->plan;

        if (!$user || !$plan) {
            return;
        }

        try {
            app(\App\Services\FirebaseService::class)->sendLeadBlockedNotification(
                $user,
                'plan_activated',
                'Abonnement renouvelé',
                'Votre abonnement ' . $plan->label . ' a été renouvelé automatiquement.',
            );
        } catch (\Exception) {}
    }

    private function notifyPaymentFailedAndCancelled(Subscription $subscription): void
    {
        $user = $subscription->user;
        $plan = $subscription->plan;

        if (!$user || !$plan) {
            return;
        }

        // Push notification
        try {
            app(\App\Services\FirebaseService::class)->sendLeadBlockedNotification(
                $user,
                'payment_failed',
                'Paiement échoué',
                'Votre paiement n\'a pas pu être traité. Votre abonnement ' . $plan->label . ' a été annulé.',
            );
        } catch (\Exception) {}

        // Email
        try {
            Mail::to($user->email)->send(new SystemNotificationMail(
                recipientName: $user->first_name,
                title:         'Abonnement annulé',
                body:          'Votre paiement pour l\'abonnement <strong>' . $plan->label . '</strong> n\'a pas pu être traité. Votre abonnement a été annulé — vous êtes revenu au plan Basic. Vous pouvez vous réabonner à tout moment.',
                actionLabel:   'Me réabonner',
                actionUrl:     config('app.url'),
            ));
        } catch (\Exception) {}
    }
}
