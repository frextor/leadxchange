<?php

namespace App\Http\Controllers;

use App\Models\PointsHistory;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Stripe\StripeClient;

class PointsController extends Controller
{
    /** Page principale : solde + historique */
    public function index(Request $request): View
    {
        $user    = $request->user();
        $balance = (int) ($user->points_balance ?? 0);

        $history = PointsHistory::where('user_id', $user->id)
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        $pricePerPoint   = (float) SystemSetting::get('points.price_per_unit', 5.00);
        $stripeEnabled   = (bool)  config('services.stripe.secret');

        return view('points.index', compact('user', 'balance', 'history', 'pricePerPoint', 'stripeEnabled'));
    }

    /** Lance un checkout Stripe pour acheter N points librement */
    public function buy(Request $request): RedirectResponse
    {
        $request->validate(['quantity' => 'required|integer|min:1|max:50']);

        $user     = $request->user();
        $quantity = (int) $request->input('quantity');

        if (! config('services.stripe.secret')) {
            return back()->with('error', 'Le paiement en ligne n\'est pas disponible pour le moment.');
        }

        $pricePerPoint = (float) SystemSetting::get('points.price_per_unit', 5.00);
        $totalCents    = (int) round($quantity * $pricePerPoint * 100);

        if ($totalCents <= 0) {
            return back()->with('error', 'Montant invalide.');
        }

        $stripe = new StripeClient(config('services.stripe.secret'));

        // Créer ou réutiliser le customer Stripe
        if (! $user->stripe_customer_id) {
            $customer = $stripe->customers->create([
                'email'    => $user->email,
                'name'     => trim("{$user->first_name} {$user->last_name}"),
                'metadata' => ['user_id' => (string) $user->id],
            ]);
            $user->forceFill(['stripe_customer_id' => $customer->id])->save();
        }

        $currency = config('services.stripe.currency', 'eur');

        $session = $stripe->checkout->sessions->create([
            'customer'    => $user->stripe_customer_id,
            'mode'        => 'payment',
            'line_items'  => [[
                'quantity'   => 1,
                'price_data' => [
                    'currency'     => $currency,
                    'unit_amount'  => $totalCents,
                    'product_data' => [
                        'name'        => "Achat de {$quantity} point(s) LeadXchange",
                        'description' => "{$quantity} point(s) crédités sur votre compte",
                    ],
                ],
            ]],
            'success_url' => route('points.buy.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'  => route('points.index') . '?canceled=1',
            'metadata'    => [
                'user_id'       => (string) $user->id,
                'points_to_add' => (string) $quantity,
                'type'          => 'points_free_purchase',
            ],
        ]);

        return redirect($session->url);
    }

    /** Retour après paiement réussi */
    public function success(Request $request): RedirectResponse
    {
        $sessionId = $request->get('session_id');
        if (! $sessionId) {
            return redirect()->route('points.index');
        }

        $stripe  = new StripeClient(config('services.stripe.secret'));
        $session = $stripe->checkout->sessions->retrieve($sessionId);

        if (
            ($session->payment_status === 'paid' || $session->status === 'complete')
            && ($session->metadata->type ?? '') === 'points_free_purchase'
        ) {
            $userId      = $session->metadata->user_id ?? null;
            $pointsToAdd = (int) ($session->metadata->points_to_add ?? 0);

            if ($userId && $pointsToAdd > 0) {
                $user = \App\Models\User::find($userId);
                $user?->adjustPoints($pointsToAdd, 'points_purchased');
            }
        }

        return redirect()->route('points.index')
            ->with('success', '🎉 Points achetés avec succès ! Votre solde a été mis à jour.');
    }
}
