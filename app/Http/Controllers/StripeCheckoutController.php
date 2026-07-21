<?php

namespace App\Http\Controllers;

use App\Mail\SystemNotificationMail;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

class StripeCheckoutController extends Controller
{
    private function stripe(): StripeClient
    {
        return new StripeClient(config('services.stripe.secret'));
    }

    /** Redirect to Stripe Checkout for a given plan. */
    public function checkout(Plan $plan, Request $request): RedirectResponse
    {
        if ((float) $plan->price <= 0) {
            return redirect()->route('upgrade')->with('error', 'Ce plan est gratuit.');
        }

        if (! $plan->stripe_price_id) {
            return redirect()->route('upgrade')->with('error', 'Ce plan n\'est pas encore disponible au paiement en ligne. Contactez-nous.');
        }

        $billingPeriod = $request->input('billing_period', 'monthly');

        // Use annual Stripe price if requested and configured
        if ($billingPeriod === 'annual' && $plan->stripe_annual_price_id) {
            $stripePriceId = $plan->stripe_annual_price_id;
        } else {
            $stripePriceId  = $plan->stripe_price_id;
            $billingPeriod  = 'monthly';
        }

        $user   = $request->user();
        $stripe = $this->stripe();

        // Ensure Stripe customer
        if (! $user->stripe_customer_id) {
            $customer = $stripe->customers->create([
                'email'    => $user->email,
                'name'     => trim("{$user->first_name} {$user->last_name}"),
                'metadata' => ['user_id' => (string) $user->id],
            ]);
            $user->forceFill(['stripe_customer_id' => $customer->id])->save();
        }

        $session = $stripe->checkout->sessions->create([
            'customer'              => $user->stripe_customer_id,
            'mode'                  => 'subscription',
            'line_items'            => [['price' => $stripePriceId, 'quantity' => 1]],
            'success_url'           => route('checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'            => route('upgrade') . '?canceled=1',
            'allow_promotion_codes' => true,
            'metadata'              => [
                'user_id'        => (string) $user->id,
                'plan_id'        => (string) $plan->id,
                'billing_period' => $billingPeriod,
            ],
            'subscription_data' => [
                'metadata' => [
                    'user_id'        => (string) $user->id,
                    'plan_id'        => (string) $plan->id,
                    'billing_period' => $billingPeriod,
                ],
            ],
        ]);

        return redirect($session->url);
    }

    /** Handle successful checkout return. */
    public function success(Request $request): RedirectResponse
    {
        $sessionId = $request->get('session_id');
        if (! $sessionId) {
            return redirect()->route('dashboard');
        }

        $stripe  = $this->stripe();
        $session = $stripe->checkout->sessions->retrieve($sessionId, ['expand' => ['subscription']]);

        if ($session->payment_status === 'paid' || $session->status === 'complete') {
            $userId = $session->metadata->user_id ?? null;
            $planId = $session->metadata->plan_id ?? null;

            if ($userId && $planId) {
                $sub           = $session->subscription;
                $billingPeriod = $session->metadata->billing_period ?? 'monthly';

                Subscription::updateOrCreate(
                    ['user_id' => $userId],
                    [
                        'plan_id'                => $planId,
                        'status'                 => 'active',
                        'billing_period'         => $billingPeriod,
                        'stripe_subscription_id' => $sub?->id,
                        'stripe_status'          => $sub?->status ?? 'active',
                        'current_period_end'     => $sub?->current_period_end
                            ? \Carbon\Carbon::createFromTimestamp($sub->current_period_end)
                            : null,
                        'cancel_at_period_end'   => (bool) ($sub?->cancel_at_period_end ?? false),
                    ]
                );

                try {
                    $notifUser = User::find($userId);
                    $notifPlan = Plan::find($planId);
                    if ($notifUser && $notifPlan) {
                        ActivityLogger::log(
                            'payment.plan_purchased',
                            "Plan {$notifPlan->label} acheté (web checkout) par {$notifUser->first_name} {$notifUser->last_name}",
                            $notifUser->id,
                            $notifUser,
                            ['plan_label' => $notifPlan->label, 'stripe_session_id' => $sessionId],
                        );
                        Mail::to($notifUser->email)->send(new SystemNotificationMail(
                            recipientName: $notifUser->first_name,
                            title:         'Votre plan ' . $notifPlan->label . ' est activé !',
                            body:          'Merci pour votre abonnement <strong>' . $notifPlan->label . '</strong>. Votre plan est maintenant actif — profitez de toutes les fonctionnalités LeadXchange !',
                            actionLabel:   'Accéder à mon dashboard',
                            actionUrl:     route('dashboard'),
                            templateKey:   'plan_purchased',
                            extraVars:     ['plan_label' => $notifPlan->label],
                        ));
                    }
                } catch (\Throwable) {}
            }
        }

        return redirect()->route('dashboard')
            ->with('success', 'Abonnement activé ! Bienvenue dans votre nouveau plan.');
    }

    /** Handle Stripe webhooks (subscription updates, cancellations…). */
    public function webhook(Request $request): Response
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (SignatureVerificationException) {
            return response('Invalid signature', 400);
        }

        match ($event->type) {
            'customer.subscription.updated',
            'customer.subscription.deleted' => $this->handleSubscriptionChange($event->data->object),
            default                          => null,
        };

        return response('OK', 200);
    }

    private function handleSubscriptionChange(object $sub): void
    {
        $userId = $sub->metadata->user_id ?? null;
        if (! $userId) return;

        $isActive = in_array($sub->status, ['active', 'trialing']);

        Subscription::where('user_id', $userId)
            ->where('stripe_subscription_id', $sub->id)
            ->update([
                'status'               => $isActive ? 'active' : 'canceled',
                'stripe_status'        => $sub->status,
                'current_period_end'   => $sub->current_period_end
                    ? \Carbon\Carbon::createFromTimestamp($sub->current_period_end)
                    : null,
                'cancel_at_period_end' => (bool) $sub->cancel_at_period_end,
            ]);
    }
}
