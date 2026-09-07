<?php

namespace App\Http\Controllers;

use App\Models\PointsPayment;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;
use Stripe\StripeClient;
use Stripe\Webhook;

class PointsPurchaseController extends Controller
{
    private function stripe(): StripeClient
    {
        return new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Start a Stripe checkout to buy enough points to get back to 0.
     */
    public function checkout(Request $request): RedirectResponse
    {
        $user = $request->user()->loadMissing('subscription.plan');

        $balance = (int) ($user->points_balance ?? 0);

        if ($balance >= 0) {
            return redirect()->route('dashboard')->with('info', 'Votre solde est déjà positif.');
        }

        if (! config('services.stripe.secret')) {
            return redirect()->route('dashboard')->with('error', 'Le paiement en ligne n\'est pas disponible pour le moment.');
        }

        $pointsNeeded = abs($balance); // exact amount to reach 0
        $pricePerPoint = (float) SystemSetting::get('points.price_per_unit', 5.00); // € per point
        $totalAmount = (int) round($pointsNeeded * $pricePerPoint * 100); // in cents

        if ($totalAmount <= 0) {
            return redirect()->route('dashboard')->with('error', 'Montant invalide.');
        }

        $currency = config('services.stripe.currency', 'eur');
        $stripe   = $this->stripe();

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
            'customer'    => $user->stripe_customer_id,
            'mode'        => 'payment',
            'line_items'  => [[
                'quantity'   => 1,
                'price_data' => [
                    'currency'     => $currency,
                    'unit_amount'  => $totalAmount,
                    'product_data' => [
                        'name'        => "Achat de {$pointsNeeded} point(s) LeadXchange",
                        'description' => "Remise à zéro de votre solde ({$balance} pts → 0 pts)",
                    ],
                ],
            ]],
            'success_url' => route('points.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('dashboard') . '?points_canceled=1',
            'metadata'    => [
                'user_id'       => (string) $user->id,
                'points_to_add' => (string) $pointsNeeded,
                'amount_cents'  => (string) $totalAmount,
                'currency'      => $currency,
                'type'          => 'points_purchase',
            ],
        ]);

        return redirect($session->url);
    }

    /**
     * Handle successful points purchase.
     */
    public function success(Request $request): RedirectResponse
    {
        $sessionId = $request->get('session_id');
        if (! $sessionId) {
            return redirect()->route('dashboard');
        }

        $session = $this->stripe()->checkout->sessions->retrieve($sessionId);
        $this->creditPointsOnce($session);

        return redirect()->route('dashboard')
            ->with('success', 'Points achetés avec succès ! Votre solde est remis à zéro.');
    }

    /**
     * Stripe webhook for payment_intent.succeeded (fallback).
     */
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

        if ($event->type === 'checkout.session.completed') {
            $this->creditPointsOnce($event->data->object);
        }

        return response('OK', 200);
    }

    /**
     * Crédite les points une seule fois par session Stripe (idempotent).
     * Utilisé à la fois par le retour navigateur (success) et le webhook —
     * lequel des deux arrive en premier gagne, le second est un no-op.
     */
    private function creditPointsOnce(object $session): void
    {
        if (
            ($session->payment_status ?? null) !== 'paid'
            && ($session->status ?? null) !== 'complete'
        ) {
            return;
        }
        if (($session->metadata->type ?? '') !== 'points_purchase') {
            return;
        }

        $userId      = $session->metadata->user_id ?? null;
        $pointsToAdd = (int) ($session->metadata->points_to_add ?? 0);

        if (! $userId || $pointsToAdd <= 0) {
            return;
        }

        try {
            PointsPayment::create([
                'user_id'                  => $userId,
                'stripe_session_id'        => $session->id,
                'stripe_payment_intent_id' => $session->payment_intent ?? null,
                'points_purchased'         => $pointsToAdd,
                'amount_cents'             => (int) ($session->metadata->amount_cents ?? $session->amount_total ?? 0),
                'currency'                 => $session->metadata->currency ?? $session->currency ?? 'eur',
                'source'                   => 'negative_balance_topup',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Contrainte unique sur stripe_session_id → déjà crédité (webhook + retour navigateur).
            return;
        }

        User::find($userId)?->adjustPoints($pointsToAdd, 'points_purchased');
    }
}
