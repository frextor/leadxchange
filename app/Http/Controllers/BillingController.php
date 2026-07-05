<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Stripe\StripeClient;

class BillingController extends Controller
{
    /** §12.3 — Page facturation & abonnement */
    public function index(Request $request): View
    {
        $user         = $request->user();
        $subscription = $user->subscription()->with('plan')->first();
        $plans        = Plan::where('is_active', true)->orderBy('sort_order')->get();
        $invoices     = $this->fetchStripeInvoices($user);

        return view('account.billing', compact('user', 'subscription', 'plans', 'invoices'));
    }

    private function fetchStripeInvoices($user): array
    {
        $secret = config('services.stripe.secret');
        if (!$secret) return [];

        $customerId = $user->stripe_customer_id
            ?? $user->subscription()?->first()?->stripe_customer_id
            ?? null;

        if (!$customerId) return [];

        try {
            $stripe   = new StripeClient($secret);
            $invoices = $stripe->invoices->all([
                'customer' => $customerId,
                'limit'    => 24,
                'status'   => 'paid',
            ]);

            return collect($invoices->data)->map(fn ($inv) => [
                'id'          => $inv->id,
                'number'      => $inv->number ?? $inv->id,
                'amount'      => $inv->amount_paid / 100,
                'currency'    => strtoupper($inv->currency),
                'date'        => date('d/m/Y', $inv->created),
                'period'      => $inv->period_start && $inv->period_end
                                    ? date('d/m/Y', $inv->period_start) . ' – ' . date('d/m/Y', $inv->period_end)
                                    : null,
                'pdf_url'     => $inv->invoice_pdf ?? null,
                'receipt_url' => $inv->hosted_invoice_url ?? null,
                'description' => $inv->lines?->data[0]?->description ?? 'Abonnement LeadXchange',
            ])->toArray();
        } catch (\Throwable $e) {
            Log::warning('BillingController::fetchStripeInvoices failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /** §12.5 — Résilier l'abonnement (fin de période en cours) */
    public function cancel(Request $request): RedirectResponse
    {
        $user         = $request->user();
        $subscription = $user->subscription()->first();

        if (!$subscription || $subscription->status !== 'active') {
            return back()->with('error', 'Aucun abonnement actif à résilier.');
        }

        // Marquer la résiliation pour fin de période
        $subscription->update([
            'cancel_at_period_end' => true,
            'status'               => 'active', // reste actif jusqu'à la fin
        ]);

        // Si Stripe gère : annuler côté Stripe aussi
        if ($subscription->stripe_subscription_id && config('services.stripe.secret')) {
            try {
                $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
                $stripe->subscriptions->update($subscription->stripe_subscription_id, [
                    'cancel_at_period_end' => true,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Stripe cancel failed', ['error' => $e->getMessage()]);
            }
        }

        $endDate = $subscription->current_period_end?->format('d/m/Y') ?? 'la fin de la période';

        return back()->with('success',
            "Votre abonnement sera résilié le {$endDate}. Vous conservez l'accès jusqu'à cette date (CGU §12.5)."
        );
    }

    /** Réactiver un abonnement avant sa résiliation effective */
    public function reactivate(Request $request): RedirectResponse
    {
        $user         = $request->user();
        $subscription = $user->subscription()->first();

        if (!$subscription || !$subscription->cancel_at_period_end) {
            return back()->with('error', 'Aucune résiliation en attente à annuler.');
        }

        $subscription->update(['cancel_at_period_end' => false]);

        if ($subscription->stripe_subscription_id && config('services.stripe.secret')) {
            try {
                $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
                $stripe->subscriptions->update($subscription->stripe_subscription_id, [
                    'cancel_at_period_end' => false,
                ]);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Stripe reactivate failed', ['error' => $e->getMessage()]);
            }
        }

        return back()->with('success', 'Résiliation annulée. Votre abonnement continue normalement.');
    }
}
