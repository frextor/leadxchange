<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\EventPayment;
use App\Models\PointsPayment;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentsController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'subscriptions');

        // ── Subscriptions ─────────────────────────────────────────────────────
        $subQuery = Subscription::with(['user', 'plan'])
            ->whereNotNull('stripe_subscription_id');

        if ($request->filled('plan_id')) {
            $subQuery->where('plan_id', $request->plan_id);
        }
        if ($request->filled('status')) {
            $subQuery->where('status', $request->status);
        }
        if ($request->filled('renewing_soon')) {
            $subQuery->where('status', 'active')
                     ->whereBetween('current_period_end', [now(), now()->addDays(7)]);
        }
        if ($request->filled('billing_period')) {
            $subQuery->where('billing_period', $request->billing_period);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $subQuery->whereHas('user', fn($q) => $q
                ->where('first_name', 'like', "%{$s}%")
                ->orWhere('last_name',  'like', "%{$s}%")
                ->orWhere('email',      'like', "%{$s}%")
            );
        }

        $subscriptions = $subQuery->orderByDesc('created_at')->paginate(30)->withQueryString();

        // ── Event payments ────────────────────────────────────────────────────
        $evtQuery = EventPayment::with(['user', 'event'])
            ->whereNotNull('stripe_payment_intent_id');

        if ($request->filled('evt_status')) {
            $evtQuery->where('status', $request->evt_status);
        }
        if ($request->filled('search') && $tab === 'events') {
            $s = $request->search;
            $evtQuery->whereHas('user', fn($q) => $q
                ->where('first_name', 'like', "%{$s}%")
                ->orWhere('last_name',  'like', "%{$s}%")
                ->orWhere('email',      'like', "%{$s}%")
            );
        }

        $eventPayments = $evtQuery->orderByDesc('created_at')->paginate(30)->withQueryString();

        // ── Points purchases ─────────────────────────────────────────────────────
        $pointsQuery = PointsPayment::with('user');

        if ($request->filled('search') && $tab === 'points') {
            $s = $request->search;
            $pointsQuery->whereHas('user', fn($q) => $q
                ->where('first_name', 'like', "%{$s}%")
                ->orWhere('last_name',  'like', "%{$s}%")
                ->orWhere('email',      'like', "%{$s}%")
            );
        }

        $pointsPayments = $pointsQuery->orderByDesc('created_at')->paginate(30)->withQueryString();

        // ── Stats ─────────────────────────────────────────────────────────────
        $stats = [
            'active_subs'    => Subscription::where('status', 'active')->whereNotNull('stripe_subscription_id')->count(),
            'mrr'            => Subscription::with('plan')->where('status', 'active')->whereNotNull('stripe_subscription_id')
                                    ->get()->sum(fn($s) => (float) ($s->plan?->price ?? 0)),
            'event_revenue'  => EventPayment::where('status', 'succeeded')->sum('amount') / 100,
            'evt_count'      => EventPayment::where('status', 'succeeded')->count(),
            'points_revenue' => PointsPayment::where('status', 'succeeded')->sum('amount_cents') / 100,
            'points_count'   => PointsPayment::where('status', 'succeeded')->count(),
            'points_sold'    => PointsPayment::where('status', 'succeeded')->sum('points_purchased'),
        ];

        $plans = \App\Models\Plan::where('price', '>', 0)->orderBy('sort_order')->get(['id', 'label']);

        return view('admin.super_admin.payments.index', compact(
            'tab', 'subscriptions', 'eventPayments', 'pointsPayments', 'stats', 'plans'
        ));
    }
}
