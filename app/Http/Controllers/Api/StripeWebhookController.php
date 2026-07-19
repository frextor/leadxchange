<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\SystemNotificationMail;
use App\Models\BalancePurchase;
use App\Models\EventPayment;
use App\Services\ActivityLogger;
use App\Services\PointsService;
use App\Models\EventInvitation;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $event = Webhook::constructEvent(
                $request->getContent(),
                $request->header('Stripe-Signature'),
                config('services.stripe.webhook_secret'),
            );
        } catch (UnexpectedValueException|SignatureVerificationException) {
            return response()->json(['message' => 'Invalid Stripe webhook signature.'], 400);
        }

        $object = $event->data->object;

        match ($event->type) {
            'payment_intent.succeeded' => $this->handlePaymentIntentSucceeded($object),
            'payment_intent.payment_failed' => $this->handlePaymentIntentFailed($object),
            'payment_intent.canceled' => $this->handlePaymentIntentCanceled($object),
            'customer.subscription.created',
            'customer.subscription.updated',
            'customer.subscription.deleted' => $this->syncSubscription($object),
            'invoice.payment_succeeded' => $this->syncInvoiceSubscription($object),
            'invoice.payment_failed'   => $this->handleInvoicePaymentFailed($object),
            default => null,
        };

        return response()->json(['received' => true]);
    }

    private function handlePaymentIntentSucceeded(object $paymentIntent): void
    {
        $type = $paymentIntent->metadata?->type ?? null;

        if ($type === 'balance_purchase') {
            $this->handleBalancePurchaseSucceeded($paymentIntent);
            return;
        }

        if ($type !== 'event_registration') {
            return;
        }

        DB::transaction(function () use ($paymentIntent) {
            $payment = EventPayment::where('stripe_payment_intent_id', $paymentIntent->id)
                ->lockForUpdate()
                ->first();

            if (!$payment) {
                return;
            }

            $payment->update([
                'status' => 'succeeded',
                'failure_message' => null,
            ]);

            $event = $payment->event()->lockForUpdate()->first();
            if (!$event || $event->isAttending($payment->user_id)) {
                return;
            }

            if ($event->max_attendees !== null && $event->attendees_count >= $event->max_attendees) {
                return;
            }

            $event->attendees()->attach($payment->user_id, ['role' => 'attendee']);
            $event->increment('attendees_count');

            EventInvitation::where('event_id', $event->id)
                ->where('user_id', $payment->user_id)
                ->where('status', 'pending')
                ->update(['status' => 'accepted']);
        });
    }

    private function handleBalancePurchaseSucceeded(object $paymentIntent): void
    {
        DB::transaction(function () use ($paymentIntent) {
            $purchase = BalancePurchase::where('stripe_payment_intent_id', $paymentIntent->id)
                ->lockForUpdate()
                ->first();

            if (!$purchase || $purchase->status === 'succeeded') {
                return;
            }

            $purchase->update(['status' => 'succeeded']);

            $user = User::find($purchase->user_id);
            if (!$user) return;

            app(PointsService::class)->adjust($user, $purchase->points, 'balance_purchase');
        });
    }

    private function handlePaymentIntentFailed(object $paymentIntent): void
    {
        $this->markEventPaymentFailed(
            $paymentIntent,
            'failed',
            $paymentIntent->last_payment_error?->message ?? null,
        );
    }

    private function handlePaymentIntentCanceled(object $paymentIntent): void
    {
        $this->markEventPaymentFailed($paymentIntent, 'canceled', null);
    }

    private function markEventPaymentFailed(object $paymentIntent, string $status, ?string $message): void
    {
        if (($paymentIntent->metadata?->type ?? null) !== 'event_registration') {
            return;
        }

        EventPayment::where('stripe_payment_intent_id', $paymentIntent->id)
            ->update([
                'status' => $status,
                'failure_message' => $message,
            ]);
    }

    private function syncInvoiceSubscription(object $invoice): void
    {
        if (!$invoice->subscription) {
            return;
        }

        $subscription = \Stripe\Subscription::retrieve($invoice->subscription);
        $this->syncSubscription($subscription);
    }

    private function handleInvoicePaymentFailed(object $invoice): void
    {
        // Keep subscription state in sync with Stripe
        $this->syncInvoiceSubscription($invoice);

        if (!$invoice->subscription || !$invoice->customer) {
            return;
        }

        $user = User::where('stripe_customer_id', $invoice->customer)->first();
        if (!$user) {
            return;
        }

        // Push notification
        try {
            app(\App\Services\FirebaseService::class)->sendLeadBlockedNotification(
                $user,
                'payment_failed',
                'Paiement échoué',
                'Votre paiement n\'a pas pu être traité. Votre abonnement sera annulé si le problème persiste.',
            );
        } catch (\Exception) {}

        // Email
        try {
            Mail::to($user->email)->send(new \App\Mail\SystemNotificationMail(
                recipientName: $user->first_name,
                title:         'Paiement échoué',
                body:          'Votre paiement pour l\'abonnement LeadXchange n\'a pas pu être traité. Veuillez mettre à jour votre moyen de paiement pour conserver votre accès Premium.',
                actionLabel:   'Gérer mon abonnement',
                actionUrl:     config('app.url'),
            ));
        } catch (\Exception) {}
    }

    private function syncSubscription(object $stripeSubscription): void
    {
        $userId = $stripeSubscription->metadata?->user_id ?? null;
        $planId = $stripeSubscription->metadata?->plan_id ?? null;

        $user = $userId ? User::find((int) $userId) : User::where('stripe_customer_id', $stripeSubscription->customer)->first();
        $plan = $planId ? Plan::find((int) $planId) : null;

        if (!$user || !$plan) {
            return;
        }

        $previousStatus = Subscription::where('stripe_subscription_id', $stripeSubscription->id)
            ->value('status');

        $localStatus = $this->localSubscriptionStatus($stripeSubscription->status);

        DB::transaction(function () use ($user, $plan, $stripeSubscription, $localStatus) {

            // If Stripe reports this subscription as active but it is scheduled for
            // cancellation (cancel_at_period_end=true), and the user already has a
            // *newer* active subscription, treat this one as canceled locally.
            // This prevents a delayed Stripe webhook from a superseded subscription
            // from overwriting the current active plan (e.g. VIP→Enterprise upgrade).
            if ($localStatus === 'active' && $stripeSubscription->cancel_at_period_end) {
                $hasNewerActive = $user->subscriptions()
                    ->where('status', 'active')
                    ->whereNotNull('stripe_subscription_id')
                    ->where('stripe_subscription_id', '!=', $stripeSubscription->id)
                    ->exists();

                if ($hasNewerActive) {
                    $localStatus = 'canceled';
                }
            }

            if ($localStatus === 'active') {
                $currentLocalSubscriptionId = optional($user->subscriptions()
                    ->where('stripe_subscription_id', $stripeSubscription->id)
                    ->first())->id;

                $oldSubscriptions = $user->subscriptions()
                    ->when($currentLocalSubscriptionId, fn($query) => $query->where('id', '!=', $currentLocalSubscriptionId))
                    ->where('status', 'active')
                    ->whereNotNull('stripe_subscription_id')
                    ->get();

                foreach ($oldSubscriptions as $oldSubscription) {
                    try {
                        \Stripe\Subscription::update($oldSubscription->stripe_subscription_id, [
                            'cancel_at_period_end' => true,
                        ]);
                    } catch (\Throwable) {
                        // Keep local state consistent even if Stripe cancellation is retried manually later.
                    }
                }

                $user->subscriptions()
                    ->when($currentLocalSubscriptionId, fn($query) => $query->where('id', '!=', $currentLocalSubscriptionId))
                    ->update(['status' => 'canceled', 'cancel_at_period_end' => true]);
            }

            Subscription::updateOrCreate(
                ['stripe_subscription_id' => $stripeSubscription->id],
                [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'status' => $localStatus,
                    'stripe_status' => $stripeSubscription->status,
                    'current_period_end' => $this->resolveTestPeriodEnd($stripeSubscription),
                    'cancel_at_period_end' => (bool) $stripeSubscription->cancel_at_period_end,
                    'ends_at' => $stripeSubscription->ended_at
                        ? Carbon::createFromTimestamp($stripeSubscription->ended_at)
                        : null,
                ],
            );
        });

        // Send confirmation email only on first activation (not on renewals)
        if ($localStatus === 'active' && $previousStatus !== 'active') {
            ActivityLogger::log(
                'payment.plan_activated',
                "Plan {$plan->label} activé (mobile/webhook) pour {$user->first_name} {$user->last_name}",
                $user->id,
                $user,
                ['plan_label' => $plan->label, 'stripe_subscription_id' => $stripeSubscription->id],
            );

            try {
                Mail::to($user->email)->send(new SystemNotificationMail(
                    recipientName: $user->first_name,
                    title:         'Votre plan ' . $plan->label . ' est activé !',
                    body:          'Merci pour votre abonnement <strong>' . $plan->label . '</strong>. Votre plan est maintenant actif — profitez de toutes les fonctionnalités LeadXchange !',
                    actionLabel:   'Accéder à mon dashboard',
                    actionUrl:     route('dashboard'),
                    templateKey:   'plan_purchased',
                    extraVars:     ['plan_label' => $plan->label],
                ));
            } catch (\Throwable) {}
        }
    }

    private function localSubscriptionStatus(string $stripeStatus): string
    {
        return in_array($stripeStatus, ['active', 'trialing'], true) ? 'active' : 'canceled';
    }

    private function resolveTestPeriodEnd(object $stripeSubscription): ?Carbon
    {
        if ((bool) \App\Models\SystemSetting::get('payments.subscription_test_mode')) {
            $interval = $stripeSubscription->items?->data[0]?->price?->recurring?->interval ?? 'month';
            $key      = $interval === 'year'
                ? 'payments.subscription_test_annual_minutes'
                : 'payments.subscription_test_monthly_minutes';
            $minutes  = (int) (\App\Models\SystemSetting::get($key) ?: 5);
            return Carbon::now()->addMinutes($minutes);
        }
        return $stripeSubscription->current_period_end
            ? Carbon::createFromTimestamp($stripeSubscription->current_period_end)
            : null;
    }
}
