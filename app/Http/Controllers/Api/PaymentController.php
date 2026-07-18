<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BalancePurchase;
use App\Models\Event;
use App\Models\EventPayment;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Exception\InvalidRequestException;
use Stripe\Customer;
use Stripe\EphemeralKey;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Subscription as StripeSubscription;

class PaymentController extends Controller
{
    public function config(): JsonResponse
    {
        return response()->json([
            'publishable_key'       => config('services.stripe.publishable'),
            'merchant_display_name' => config('app.name', 'LeadXchange'),
            'point_price_cents'     => (int) SystemSetting::get('payments.point_price_cents', 100),
        ]);
    }

    public function balanceIntent(Request $request): JsonResponse
    {
        $this->configureStripe();

        $request->validate([
            'points' => ['required', 'integer', 'min:1', 'max:500'],
        ]);

        $user = $request->user();
        $points = (int) $request->input('points');
        $priceCents = (int) SystemSetting::get('payments.point_price_cents', 100);
        $amount = $points * $priceCents;
        $currency = config('services.stripe.currency', 'eur');

        $customerId = $this->ensureStripeCustomer($user);

        $paymentIntent = PaymentIntent::create([
            'amount'   => $amount,
            'currency' => $currency,
            'customer' => $customerId,
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => [
                'type'    => 'balance_purchase',
                'user_id' => (string) $user->id,
                'points'  => (string) $points,
            ],
        ]);

        BalancePurchase::create([
            'user_id'                   => $user->id,
            'stripe_payment_intent_id'  => $paymentIntent->id,
            'points'                    => $points,
            'amount_cents'              => $amount,
            'currency'                  => $currency,
            'status'                    => $paymentIntent->status,
        ]);

        return response()->json($this->paymentSheetPayload([
            'payment_intent_id' => $paymentIntent->id,
            'client_secret'     => $paymentIntent->client_secret,
            'customer_id'       => $customerId,
        ]));
    }

    public function eventIntent(int $eventId, Request $request): JsonResponse
    {
        $this->configureStripe();

        $event = Event::findOrFail($eventId);
        $user = $request->user();

        if ($event->is_free) {
            return response()->json(['message' => 'This event is free. Use the regular join endpoint.'], 422);
        }

        // Already paid: re-attach if they left (no re-charge), return empty client_secret as signal
        if (EventPayment::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->where('status', 'succeeded')
            ->exists()) {
            if (!$event->isAttending($user->id)) {
                $event->attendees()->attach($user->id, ['role' => 'attendee']);
                $event->increment('attendees_count');
            }
            return response()->json([
                'client_secret'   => '',
                'customer_id'     => '',
                'ephemeral_key'   => '',
                'publishable_key' => '',
            ]);
        }

        if ($event->isAttending($user->id)) {
            return response()->json(['message' => 'Already registered for this event.'], 422);
        }

        if ($event->max_attendees !== null && $event->attendees_count >= $event->max_attendees) {
            return response()->json(['message' => 'Event is at full capacity.'], 422);
        }

        $customerId = $this->ensureStripeCustomer($user);
        $amount = (int) round(((float) $event->price) * 100);
        $currency = config('services.stripe.currency', 'eur');

        $paymentIntent = PaymentIntent::create([
            'amount' => $amount,
            'currency' => $currency,
            'customer' => $customerId,
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => [
                'type' => 'event_registration',
                'user_id' => (string) $user->id,
                'event_id' => (string) $event->id,
            ],
        ]);

        EventPayment::updateOrCreate(
            ['stripe_payment_intent_id' => $paymentIntent->id],
            [
                'user_id' => $user->id,
                'event_id' => $event->id,
                'amount' => $amount,
                'currency' => $currency,
                'status' => $paymentIntent->status,
                'failure_message' => null,
            ],
        );

        return response()->json($this->paymentSheetPayload([
            'payment_intent_id' => $paymentIntent->id,
            'client_secret' => $paymentIntent->client_secret,
            'customer_id' => $customerId,
        ]));
    }

    public function planSubscription(int $planId, Request $request): JsonResponse
    {
        $this->configureStripe();

        $plan = Plan::where('is_active', true)->findOrFail($planId);
        $user = $request->user()->loadMissing('subscription.plan');

        if ((float) $plan->price <= 0) {
            return response()->json(['message' => 'This plan does not require Stripe payment.'], 422);
        }

        $currentSubscription = $user->subscription;
        $currentPlan = $currentSubscription?->plan;

        if ($currentPlan && $currentPlan->id === $plan->id) {
            return response()->json(['message' => 'This plan is already active.'], 422);
        }

        if ($currentPlan && $this->planPriority($plan) < $this->planPriority($currentPlan)) {
            return response()->json(['message' => 'You already have a higher plan.'], 422);
        }

        $billing = $request->input('billing', 'monthly');
        $isAnnual = $billing === 'annual' && $plan->stripe_annual_price_id;

        $stripePriceId = $isAnnual
            ? $plan->stripe_annual_price_id
            : ($plan->stripe_price_id ?: config('services.stripe.premium_price_id'));

        if (!$stripePriceId) {
            return response()->json(['message' => 'Stripe price is not configured for this plan.'], 422);
        }

        $customerId = $this->ensureStripeCustomer($user);

        $stripeSubscription = StripeSubscription::create([
            'customer' => $customerId,
            'items' => [
                ['price' => $stripePriceId],
            ],
            'payment_behavior' => 'default_incomplete',
            'payment_settings' => [
                'save_default_payment_method' => 'on_subscription',
            ],
            'expand' => ['latest_invoice.payment_intent'],
            'metadata' => [
                'type' => 'plan_upgrade',
                'user_id' => (string) $user->id,
                'plan_id' => (string) $plan->id,
            ],
        ]);

        $paymentIntent = $stripeSubscription->latest_invoice?->payment_intent;
        if (!$paymentIntent || !$paymentIntent->client_secret) {
            return response()->json(['message' => 'Unable to initialize subscription payment.'], 500);
        }

        Subscription::updateOrCreate(
            ['stripe_subscription_id' => $stripeSubscription->id],
            [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => $this->localSubscriptionStatus($stripeSubscription->status),
                'stripe_status' => $stripeSubscription->status,
                'current_period_end' => $stripeSubscription->current_period_end
                    ? Carbon::createFromTimestamp($stripeSubscription->current_period_end)
                    : null,
                'cancel_at_period_end' => (bool) $stripeSubscription->cancel_at_period_end,
            ],
        );

        return response()->json($this->paymentSheetPayload([
            'subscription_id' => $stripeSubscription->id,
            'client_secret' => $paymentIntent->client_secret,
            'customer_id' => $customerId,
        ]));
    }

    public function cancelSubscription(Request $request): JsonResponse
    {
        $this->configureStripe();

        $user = $request->user()->loadMissing('subscription');
        $subscription = $user->subscription;

        if (!$subscription || !$subscription->stripe_subscription_id) {
            return response()->json(['message' => 'No active subscription found.'], 422);
        }

        if ($subscription->status !== 'active') {
            return response()->json(['message' => 'Subscription is not active.'], 422);
        }

        try {
            $stripeSubscription = StripeSubscription::retrieve($subscription->stripe_subscription_id);
            $stripeSubscription->cancel_at_period_end = true;
            $stripeSubscription->save();

            $subscription->update(['cancel_at_period_end' => true]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to cancel subscription.'], 500);
        }

        return response()->json(['message' => 'Subscription will be cancelled at the end of the billing period.']);
    }

    public function status(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'in:event,subscription'],
            'id' => ['required', 'integer'],
        ]);

        $user = $request->user();

        if ($validated['type'] === 'event') {
            $event = Event::findOrFail((int) $validated['id']);
            $payment = EventPayment::where('user_id', $user->id)
                ->where('event_id', $event->id)
                ->latest()
                ->first();

            // If payment exists but local status is still pending, verify with Stripe directly
            // (handles missed or delayed webhooks)
            if ($payment && $payment->stripe_payment_intent_id && $payment->status !== 'succeeded') {
                $this->configureStripe();
                try {
                    $pi = PaymentIntent::retrieve($payment->stripe_payment_intent_id);
                    if ($pi->status === 'succeeded') {
                        $payment->update(['status' => 'succeeded']);
                        if (!$event->isAttending($user->id)) {
                            $event->attendees()->attach($user->id, ['role' => 'attendee']);
                            $event->increment('attendees_count');
                        }
                    } elseif (in_array($pi->status, ['canceled', 'requires_payment_method'], true)) {
                        $payment->update(['status' => $pi->status]);
                    }
                } catch (\Exception) {
                    // Stripe unreachable — fall through with local status
                }
            }

            return response()->json([
                'type' => 'event',
                'event_id' => $event->id,
                'status' => $payment?->status ?? 'not_started',
                'is_attending' => $event->isAttending($user->id),
            ]);
        }

        $subscription = $user->subscriptions()
            ->where('plan_id', (int) $validated['id'])
            ->latest()
            ->first();

        return response()->json([
            'type' => 'subscription',
            'plan_id' => (int) $validated['id'],
            'status' => $subscription?->stripe_status ?? $subscription?->status ?? 'not_started',
            'current_period_end' => $subscription?->current_period_end,
        ]);
    }

    private function configureStripe(): void
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    private function ensureStripeCustomer(User $user): string
    {
        if ($user->stripe_customer_id) {
            try {
                $customer = Customer::retrieve($user->stripe_customer_id);
                if ($customer->deleted ?? false) {
                    $user->forceFill(['stripe_customer_id' => null])->save();
                } else {
                    return $user->stripe_customer_id;
                }
            } catch (InvalidRequestException) {
                $user->forceFill(['stripe_customer_id' => null])->save();
            }
        }

        $customer = Customer::create([
            'email' => $user->email,
            'name' => trim("{$user->first_name} {$user->last_name}") ?: $user->email,
            'metadata' => [
                'user_id' => (string) $user->id,
            ],
        ]);

        $user->forceFill(['stripe_customer_id' => $customer->id])->save();

        return $customer->id;
    }

    private function paymentSheetPayload(array $payload): array
    {
        $ephemeralKey = EphemeralKey::create(
            ['customer' => $payload['customer_id']],
            ['stripe_version' => '2024-06-20'],
        );

        return $payload + [
            'ephemeral_key' => $ephemeralKey->secret,
            'publishable_key' => config('services.stripe.publishable'),
        ];
    }

    private function localSubscriptionStatus(string $stripeStatus): string
    {
        return in_array($stripeStatus, ['active', 'trialing'], true) ? 'active' : 'canceled';
    }

    private function planPriority(Plan $plan): float
    {
        return (float) ($plan->sort_order ?: $plan->price);
    }
}
