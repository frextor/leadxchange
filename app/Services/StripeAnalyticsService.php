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
                    $stripe = $this->client();
                    $total  = 0.0;

                    $subscriptions = $stripe->subscriptions->all([
                        'status' => 'active',
                        'limit'  => 100,
                        'expand' => ['data.items.data.price'],
                    ]);

                    foreach ($subscriptions->autoPagingIterator() as $sub) {
                        foreach ($sub->items->data as $item) {
                            $price    = $item->price;
                            $amount   = $price->unit_amount / 100;
                            $interval = $price->recurring->interval ?? 'month';
                            $count    = $item->quantity ?? 1;

                            // Normalize all intervals to monthly
                            $monthly = match ($interval) {
                                'year'  => $amount / 12,
                                'week'  => $amount * 4.33,
                                'day'   => $amount * 30,
                                default => $amount, // month
                            };

                            $total += $monthly * $count;
                        }
                    }

                    return round($total, 2);
                } catch (\Throwable $e) {
                    Log::warning('StripeAnalyticsService::mrr Stripe failed', ['error' => $e->getMessage()]);
                }
            }

            // Fallback: local DB
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

    // ── Churn rate from Stripe subscription events ───────────────────────────

    public function churnRate(): float
    {
        return Cache::remember('stripe_analytics.churn', self::TTL, function () {
            if ($this->isConfigured()) {
                try {
                    $stripe    = $this->client();
                    $since     = now()->subDays(30)->timestamp;

                    // Count subscriptions cancelled in last 30 days
                    $cancelled = 0;
                    $events = $stripe->events->all([
                        'type'    => 'customer.subscription.deleted',
                        'created' => ['gte' => $since],
                        'limit'   => 100,
                    ]);
                    foreach ($events->autoPagingIterator() as $evt) {
                        $cancelled++;
                    }

                    // Active subscriptions count at start of period
                    $active = $this->activeSubscriptionsCountAt($stripe, now()->subDays(30)->timestamp);

                    if ($active === 0) return 0.0;

                    return round(($cancelled / $active) * 100, 2);
                } catch (\Throwable $e) {
                    Log::warning('StripeAnalyticsService::churnRate Stripe failed', ['error' => $e->getMessage()]);
                }
            }

            // Fallback: local DB
            $start         = now()->subDays(30);
            $activeAtStart = Subscription::where('status', 'active')->where('created_at', '<=', $start)->count();
            if ($activeAtStart === 0) return 0.0;
            $cancelled = Subscription::where('status', 'canceled')->where('updated_at', '>=', $start)->count();
            return round(($cancelled / $activeAtStart) * 100, 2);
        });
    }

    private function activeSubscriptionsCountAt(StripeClient $stripe, int $timestamp): int
    {
        // Approximate: active subs created before the timestamp
        $count = 0;
        $subs  = $stripe->subscriptions->all(['status' => 'active', 'limit' => 100]);
        foreach ($subs->autoPagingIterator() as $sub) {
            if ($sub->created <= $timestamp) {
                $count++;
            }
        }
        return max($count, 1);
    }

    // ── LTV = ARPU / monthly_churn_rate ──────────────────────────────────────

    public function ltv(): ?float
    {
        return Cache::remember('stripe_analytics.ltv', self::TTL, function () {
            $mrr   = $this->mrr();
            $churn = $this->churnRate();

            if ($mrr <= 0 || $churn <= 0) return null;

            // Active customer count
            $customerCount = 1;
            if ($this->isConfigured()) {
                try {
                    $subs = $this->client()->subscriptions->all(['status' => 'active', 'limit' => 1]);
                    $customerCount = max($subs->total_count ?? 1, 1);
                } catch (\Throwable) {
                    $customerCount = max(Subscription::where('status', 'active')->count(), 1);
                }
            } else {
                $customerCount = max(Subscription::where('status', 'active')->count(), 1);
            }

            $arpu = $mrr / $customerCount;
            return round($arpu / ($churn / 100), 2);
        });
    }

    // ── Monthly revenue from Stripe invoices (last N months) ─────────────────

    public function monthlyRevenueChart(int $months = 12): array
    {
        return Cache::remember("stripe_analytics.monthly_revenue_{$months}", self::TTL, function () use ($months) {
            $labels = [];
            $data   = [];
            $fr     = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

            for ($i = $months - 1; $i >= 0; $i--) {
                $d        = now()->subMonths($i)->startOfMonth();
                $key      = $d->format('Y-m');
                $labels[] = $fr[$d->month - 1] . ' ' . $d->format('y');
                $data[$key] = 0.0;
            }

            if ($this->isConfigured()) {
                try {
                    $stripe = $this->client();
                    $from   = now()->subMonths($months)->startOfMonth()->timestamp;

                    $invoices = $stripe->invoices->all([
                        'status'  => 'paid',
                        'created' => ['gte' => $from],
                        'limit'   => 100,
                    ]);

                    foreach ($invoices->autoPagingIterator() as $inv) {
                        $key = date('Y-m', $inv->created);
                        if (array_key_exists($key, $data)) {
                            $data[$key] += $inv->amount_paid / 100;
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

    // ── Recent payments from Stripe invoices ─────────────────────────────────

    public function recentPayments(int $limit = 20): array
    {
        return Cache::remember("stripe_analytics.recent_payments_{$limit}", self::TTL, function () use ($limit) {
            if ($this->isConfigured()) {
                try {
                    $stripe   = $this->client();
                    $invoices = $stripe->invoices->all([
                        'status' => 'paid',
                        'limit'  => $limit,
                        'expand' => ['data.customer', 'data.subscription'],
                    ]);

                    return collect($invoices->data)->map(fn ($inv) => [
                        'id'          => $inv->id,
                        'amount'      => $inv->amount_paid / 100,
                        'currency'    => strtoupper($inv->currency),
                        'status'      => 'paid',
                        'customer'    => $inv->customer?->email ?? $inv->customer_email ?? '—',
                        'description' => $inv->lines?->data[0]?->description
                                         ?? $inv->subscription?->description
                                         ?? 'Abonnement',
                        'date'        => date('d/m/Y', $inv->created),
                        'stripe_url'  => $inv->hosted_invoice_url ?? null,
                    ])->toArray();
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

                    $newCount = 0;
                    $newSubs  = $stripe->subscriptions->all([
                        'status'  => 'active',
                        'created' => ['gte' => $since],
                        'limit'   => 100,
                    ]);
                    foreach ($newSubs->autoPagingIterator() as $_) {
                        $newCount++;
                    }

                    $cancelledCount = 0;
                    $events = $stripe->events->all([
                        'type'    => 'customer.subscription.deleted',
                        'created' => ['gte' => $since],
                        'limit'   => 100,
                    ]);
                    foreach ($events->autoPagingIterator() as $_) {
                        $cancelledCount++;
                    }

                    return ['new' => $newCount, 'cancelled' => $cancelledCount];
                } catch (\Throwable $e) {
                    Log::warning('StripeAnalyticsService::newVsCancelled failed', ['error' => $e->getMessage()]);
                }
            }

            // Fallback: local DB
            $since = now()->subDays(30);
            return [
                'new'       => Subscription::where('created_at', '>=', $since)->count(),
                'cancelled' => Subscription::where('status', 'canceled')
                                ->where('updated_at', '>=', $since)->count(),
            ];
        });
    }

    // ── Total active subscriptions count from Stripe ─────────────────────────

    public function totalActive(): int
    {
        return Cache::remember('stripe_analytics.total_active', self::TTL, function () {
            if ($this->isConfigured()) {
                try {
                    $subs = $this->client()->subscriptions->all([
                        'status' => 'active',
                        'limit'  => 1,
                    ]);
                    return $subs->total_count ?? Subscription::where('status', 'active')->count();
                } catch (\Throwable $e) {
                    Log::warning('StripeAnalyticsService::totalActive failed', ['error' => $e->getMessage()]);
                }
            }
            return Subscription::where('status', 'active')->count();
        });
    }

    public function clearCache(): void
    {
        $keys = [
            'stripe_analytics.mrr',
            'stripe_analytics.arr',
            'stripe_analytics.churn',
            'stripe_analytics.ltv',
            'stripe_analytics.new_vs_cancelled',
            'stripe_analytics.total_active',
            'stripe_analytics.monthly_revenue_12',
            'stripe_analytics.recent_payments_20',
        ];
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}
