<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EventPayment;
use App\Models\EventInvitation;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        } catch (UnexpectedValueException|SignatureVerificationException $e) {
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
            'invoice.payment_succeeded',
            'invoice.payment_failed' => $this->syncInvoiceSubscription($object),
            default => null,
        };

        return response()->json(['received' => true]);
    }

    private function handlePaymentIntentSucceeded(object $paymentIntent): void
    {
        if (($paymentIntent->metadata?->type ?? null) !== 'event_registration') {
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

    private function syncSubscription(object $stripeSubscription): void
    {
        $userId = $stripeSubscription->metadata?->user_id ?? null;
        $planId = $stripeSubscription->metadata?->plan_id ?? null;

        $user = $userId ? User::find((int) $userId) : User::where('stripe_customer_id', $stripeSubscription->customer)->first();
        $plan = $planId ? Plan::find((int) $planId) : null;

        if (!$user || !$plan) {
            return;
        }

        DB::transaction(function () use ($user, $plan, $stripeSubscription) {
            $localStatus = $this->localSubscriptionStatus($stripeSubscription->status);

            if ($localStatus === 'active') {
                $user->subscriptions()
                    ->where('id', '!=', optional($user->subscriptions()->where('stripe_subscription_id', $stripeSubscription->id)->first())->id)
                    ->update(['status' => 'canceled']);
            }

            Subscription::updateOrCreate(
                ['stripe_subscription_id' => $stripeSubscription->id],
                [
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'status' => $localStatus,
                    'stripe_status' => $stripeSubscription->status,
                    'current_period_end' => $stripeSubscription->current_period_end
                        ? Carbon::createFromTimestamp($stripeSubscription->current_period_end)
                        : null,
                    'cancel_at_period_end' => (bool) $stripeSubscription->cancel_at_period_end,
                    'ends_at' => $stripeSubscription->ended_at
                        ? Carbon::createFromTimestamp($stripeSubscription->ended_at)
                        : null,
                ],
            );
        });
    }

    private function localSubscriptionStatus(string $stripeStatus): string
    {
        return in_array($stripeStatus, ['active', 'trialing'], true) ? 'active' : 'canceled';
    }
}
