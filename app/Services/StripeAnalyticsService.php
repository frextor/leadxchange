<?php

namespace App\Services;

use App\Models\Subscription;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class StripeAnalyticsService
{
    private const TTL = 300; // 5 min cache

    private function client(): ?StripeClient
    {
        $key = config('services.stripe.secret');
        return $key ? new StripeClient($key) : null;
    }

    public function isConfigured(): bool
    {
        return (bool) config('services.stripe.secret');
    }

    // ── MRR: sum of active Stripe subscription amounts ───────────────────────

    public function mrr(): float
    {
        return Cache::remember('stripe_analytics.mrr', self::TTL, function () {
            if ($this->isConfigured()) {
                try {
                    $total = 0.0;

                    $subs = $this->client()->subscriptions->all([
                        'status' => 'active',
                        'limit'  => 100,
                        'expand' => ['data.items.data.price'],
                    ]);

                    foreach ($subs->autoPagingIterator() as $sub) {
                        foreach ($sub->items->data as $item) {
                            $price    = $item->price;
                            $amount   = ($price->unit_amount ?? 0) / 100;
                            $interval = $price->recurring->interval ?? 'month';
                            $qty      = $item->quantity ?? 1;

                            $monthly = match ($interval) {
                                'year'  => $amount / 12,
                                'week'  => $amount * 4.33,
                                'day'   => $amount * 30,
                                default => $amount,
                            };

                            $total += $monthly * $qty;
                        }
                    }

                    return round($total, 2);
                } catch (\Throwable $e) {
                    Log::warning('StripeAnalyticsService::mrr failed', ['error' => $e->getMessage()]);
                }
            }

            return (float) DB::table('subscriptions')
                ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
                ->where('subscriptions.status', 'active')
                ->where('plans.price', '>', 0)
                ->sum('plans.price');
        });
    }

    public function arr(): float
    {
        return round($this->mrr() * 12, 2);
    }

    // ── Total active subscriptions (paginated count, no total_count) ──────────

    public function totalActive(): int
    {
        return Cache::remember('stripe_analytics.total_active', self::TTL, function () {
            if ($this->isConfigured()) {
                try {
                    $count = 0;
                    $subs  = $this->client()->subscriptions->all([
                        'status' => 'active',
                        'limit'  => 100,
                    ]);
                    foreach ($subs->autoPagingIterator() as $_) {
                        $count++;
                    }
                    return $count;
                } catch (\Throwable $e) {
                    Log::warning('StripeAnalyticsService::totalActive failed', ['error' => $e->getMessage()]);
                }
            }
            return Subscription::where('status', 'active')->count();
        });
    }

    // ── Churn rate ────────────────────────────────────────────────────────────

    public function churnRate(): float
    {
        return Cache::remember('stripe_analytics.churn', self::TTL, function () {
            if ($this->isConfigured()) {
                try {
                    $stripe = $this->client();
                    $since  = now()->subDays(30)->timestamp;

                    // Subscriptions cancelled in last 30 days
                    $cancelled = 0;
                    $events = $stripe->events->all([
                        'type'    => 'customer.subscription.deleted',
                        'created' => ['gte' => $since],
                        'limit'   => 100,
                    ]);
                    foreach ($events->autoPagingIterator() as $_) {
                        $cancelled++;
                    }

                    if ($cancelled === 0) return 0.0;

                    // Base: current active + those cancelled in period (= active at period start)
                    $currentActive = $this->totalActive();
                    $activeAtStart = max($currentActive + $cancelled, 1);

                    return round(($cancelled / $activeAtStart) * 100, 2);
                } catch (\Throwable $e) {
                    Log::warning('StripeAnalyticsService::churnRate failed', ['error' => $e->getMessage()]);
                }
            }

            $start         = now()->subDays(30);
            $activeAtStart = Subscription::where('status', 'active')->where('created_at', '<=', $start)->count();
            if ($activeAtStart === 0) return 0.0;
            $cancelled = Subscription::where('status', 'canceled')->where('updated_at', '>=', $start)->count();
            return round(($cancelled / $activeAtStart) * 100, 2);
        });
    }

    // ── LTV = ARPU / monthly_churn ────────────────────────────────────────────

    public function ltv(): ?float
    {
        return Cache::remember('stripe_analytics.ltv', self::TTL, function () {
            $mrr   = $this->mrr();
            $churn = $this->churnRate();

            if ($mrr <= 0 || $churn <= 0) return null;

            $customerCount = max($this->totalActive(), 1);
            $arpu          = $mrr / $customerCount;

            return round($arpu / ($churn / 100), 2);
        });
    }

    // ── Monthly revenue (charges API — captures ALL successful payments) ──────

    public function monthlyRevenueChart(int $months = 12): array
    {
        return Cache::remember("stripe_analytics.monthly_revenue_{$months}", self::TTL, function () use ($months) {
            $labels = [];
            $data   = [];
            $fr     = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

            for ($i = $months - 1; $i >= 0; $i--) {
                $d          = now()->subMonths($i)->startOfMonth();
                $key        = $d->format('Y-m');
                $labels[]   = $fr[$d->month - 1] . ' ' . $d->format('y');
                $data[$key] = 0.0;
            }

            if ($this->isConfigured()) {
                try {
                    $from = now()->subMonths($months)->startOfMonth()->timestamp;

                    // charges API: captures both subscription & one-time payments
                    $charges = $this->client()->charges->all([
                        'created' => ['gte' => $from],
                        'limit'   => 100,
                    ]);

                    foreach ($charges->autoPagingIterator() as $charge) {
                        if ($charge->status !== 'succeeded' || $charge->refunded) continue;
                        $key = date('Y-m', $charge->created);
                        if (array_key_exists($key, $data)) {
                            $data[$key] += ($charge->amount_captured ?? $charge->amount) / 100;
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('StripeAnalyticsService::monthlyRevenueChart failed', ['error' => $e->getMessage()]);
                    $data = $this->monthlyRevenueFromDb($months, $data);
                }
            } else {
                $data = $this->monthlyRevenueFromDb($months, $data);
            }

            return ['labels' => $labels, 'data' => array_values($data)];
        });
    }

    private function monthlyRevenueFromDb(int $months, array $data): array
    {
        $rows = DB::table('subscriptions')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->where('subscriptions.created_at', '>=', now()->subMonths($months)->startOfMonth())
            ->where('subscriptions.status', 'active')
            ->where('plans.price', '>', 0)
            ->select(
                DB::raw('YEAR(subscriptions.created_at) as yr'),
                DB::raw('MONTH(subscriptions.created_at) as mo'),
                DB::raw('SUM(plans.price) as total')
            )
            ->groupBy('yr', 'mo')
            ->get();

        foreach ($rows as $row) {
            $key = $row->yr . '-' . str_pad($row->mo, 2, '0', STR_PAD_LEFT);
            if (array_key_exists($key, $data)) {
                $data[$key] += (float) $row->total;
            }
        }

        return $data;
    }

    // ── Recent payments (charges API — all payments, not just invoices) ───────

    public function recentPayments(int $limit = 20): array
    {
        return Cache::remember("stripe_analytics.recent_payments_{$limit}", self::TTL, function () use ($limit) {
            if ($this->isConfigured()) {
                try {
                    // Fetch more than needed to account for non-succeeded charges
                    $charges = $this->client()->charges->all([
                        'limit'  => min($limit * 2, 100),
                        'expand' => ['data.customer', 'data.invoice'],
                    ]);

                    return collect($charges->data)
                        ->filter(fn ($c) => $c->status === 'succeeded' && !$c->refunded)
                        ->take($limit)
                        ->map(fn ($c) => [
                            'id'          => $c->id,
                            'amount'      => ($c->amount_captured ?? $c->amount) / 100,
                            'currency'    => strtoupper($c->currency),
                            'status'      => 'paid',
                            'customer'    => $c->customer?->email
                                             ?? $c->billing_details?->email
                                             ?? $c->receipt_email
                                             ?? '—',
                            'description' => $c->invoice?->lines?->data[0]?->description
                                             ?? $c->description
                                             ?? 'Paiement',
                            'date'        => date('d/m/Y', $c->created),
                            'stripe_url'  => $c->receipt_url ?? null,
                        ])
                        ->values()
                        ->toArray();
                } catch (\Throwable $e) {
                    Log::warning('StripeAnalyticsService::recentPayments failed', ['error' => $e->getMessage()]);
                }
            }

            return $this->recentPaymentsFromDb($limit);
        });
    }

    private function recentPaymentsFromDb(int $limit): array
    {
        return DB::table('subscriptions')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->join('users', 'subscriptions.user_id', '=', 'users.id')
            ->where('subscriptions.status', 'active')
            ->where('plans.price', '>', 0)
            ->select(
                'subscriptions.id',
                'subscriptions.created_at',
                'plans.label as description',
                'plans.price as amount',
                'users.email as customer',
            )
            ->orderByDesc('subscriptions.created_at')
            ->limit($limit)
            ->get()
            ->map(fn ($r) => [
                'id'          => 'sub_' . $r->id,
                'amount'      => (float) $r->amount,
                'currency'    => 'EUR',
                'status'      => 'paid',
                'customer'    => $r->customer,
                'description' => $r->description,
                'date'        => date('d/m/Y', strtotime($r->created_at)),
                'stripe_url'  => null,
            ])
            ->toArray();
    }

    // ── New vs cancelled (last 30 days) ──────────────────────────────────────

    public function newVsCancelled(): array
    {
        return Cache::remember('stripe_analytics.new_vs_cancelled', self::TTL, function () {
            if ($this->isConfigured()) {
                try {
                    $stripe = $this->client();
                    $since  = now()->subDays(30)->timestamp;

                    // New: subscription.created events (covers all statuses at creation)
                    $newCount = 0;
                    $newEvents = $stripe->events->all([
                        'type'    => 'customer.subscription.created',
                        'created' => ['gte' => $since],
                        'limit'   => 100,
                    ]);
                    foreach ($newEvents->autoPagingIterator() as $_) {
                        $newCount++;
                    }

                    // Cancelled: subscription.deleted events
                    $cancelledCount = 0;
                    $delEvents = $stripe->events->all([
                        'type'    => 'customer.subscription.deleted',
                        'created' => ['gte' => $since],
                        'limit'   => 100,
                    ]);
                    foreach ($delEvents->autoPagingIterator() as $_) {
                        $cancelledCount++;
                    }

                    return ['new' => $newCount, 'cancelled' => $cancelledCount];
                } catch (\Throwable $e) {
                    Log::warning('StripeAnalyticsService::newVsCancelled failed', ['error' => $e->getMessage()]);
                }
            }

            $since = now()->subDays(30);
            return [
                'new'       => Subscription::where('created_at', '>=', $since)->count(),
                'cancelled' => Subscription::where('status', 'canceled')
                                ->where('updated_at', '>=', $since)->count(),
            ];
        });
    }

    // ── Per-plan breakdown from Stripe subscriptions ──────────────────────────

    /**
     * Returns [stripe_price_id => count] for active subscriptions.
     * Used to show accurate per-plan counts on the analytics view.
     */
    public function activePlanCounts(): array
    {
        return Cache::remember('stripe_analytics.plan_counts', self::TTL, function () {
            if (!$this->isConfigured()) return [];

            try {
                $counts = [];
                $subs   = $this->client()->subscriptions->all([
                    'status' => 'active',
                    'limit'  => 100,
                    'expand' => ['data.items.data.price'],
                ]);

                foreach ($subs->autoPagingIterator() as $sub) {
                    foreach ($sub->items->data as $item) {
                        $priceId = $item->price->id ?? null;
                        if ($priceId) {
                            $counts[$priceId] = ($counts[$priceId] ?? 0) + ($item->quantity ?? 1);
                        }
                    }
                }

                return $counts;
            } catch (\Throwable $e) {
                Log::warning('StripeAnalyticsService::activePlanCounts failed', ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    public function clearCache(): void
    {
        foreach ([
            'stripe_analytics.mrr',
            'stripe_analytics.churn',
            'stripe_analytics.ltv',
            'stripe_analytics.total_active',
            'stripe_analytics.new_vs_cancelled',
            'stripe_analytics.plan_counts',
            'stripe_analytics.monthly_revenue_12',
            'stripe_analytics.recent_payments_20',
        ] as $key) {
            Cache::forget($key);
        }
    }
}
