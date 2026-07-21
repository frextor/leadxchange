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
            // In test mode, simulate Stripe's "payment failed → cancelled" notifications
            // for auto-renewing subscriptions (cancel_at_period_end = false)
            if ($testMode && !$subscription->cancel_at_period_end) {
                $this->notifyPaymentFailedAndCancelled($subscription);
            }

            $subscription->update(['status' => 'canceled']);
            $subscription->user->revokePrivilegedRolesIfBasic();
        }

        $count = $subscriptions->count();
        $this->info("Expired {$count} subscription(s)." . ($testMode ? ' [TEST MODE]' : ''));
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
