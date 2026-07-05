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

    // ── MRR / ARR from local DB (synced by webhooks) ─────────────────────────

    public function mrr(): float
    {
        return Cache::remember('stripe_analytics.mrr', self::TTL, fn () =>
            (float) DB::table('subscriptions')
                ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
                ->where('subscriptions.status', 'active')
                ->where('plans.price', '>', 0)
                ->sum('plans.price')
        );
    }

    public function arr(): float
    {
        return round($this->mrr() * 12, 2);
    }

    // ── Churn rate (subscriptions cancelled in last 30 days) ─────────────────

    public function churnRate(): float
    {
        return Cache::remember('stripe_analytics.churn', self::TTL, function () {
            $start = now()->subDays(30);

            $activeAtStart = Subscription::where('status', 'active')
                ->where('created_at', '<=', $start)
                ->count();

            if ($activeAtStart === 0) return 0.0;

            $cancelled = Subscription::where('status', 'canceled')
                ->where('updated_at', '>=', $start)
                ->count();

            return round(($cancelled / $activeAtStart) * 100, 2);
        });
    }

    // ── LTV estimate = ARPU / monthly_churn ──────────────────────────────────

    public function ltv(): ?float
    {
        return Cache::remember('stripe_analytics.ltv', self::TTL, function () {
            $activeCount = Subscription::where('status', 'active')
                ->whereHas('plan', fn ($q) => $q->where('price', '>', 0))
                ->count();

            if ($activeCount === 0) return null;

            $arpu  = $this->mrr() / $activeCount;
            $churn = $this->churnRate();

            if ($churn <= 0) return null;

            return round($arpu / ($churn / 100), 2);
        });
    }

    // ── Monthly revenue from Stripe (last 12 months via invoices) ────────────

    public function monthlyRevenueChart(int $months = 12): array
    {
        return Cache::remember("stripe_analytics.monthly_revenue_{$months}", self::TTL, function () use ($months) {
            $labels = $data = [];
            $fr = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

            for ($i = $months - 1; $i >= 0; $i--) {
                $d        = now()->subMonths($i)->startOfMonth();
                $labels[] = $fr[$d->month - 1] . ' ' . $d->format('y');
                $data[$d->format('Y-m')] = 0;
            }

            if ($this->isConfigured()) {
                try {
                    $stripe = $this->client();
                    $from   = now()->subMonths($months)->startOfMonth()->timestamp;

                    $invoices = $stripe->invoices->all([
                        'status'           => 'paid',
                        'created'          => ['gte' => $from],
                        'limit'            => 100,
                        'expand'           => [],
                    ]);

                    foreach ($invoices->autoPagingIterator() as $inv) {
                        $key = date('Y-m', $inv->created);
                        if (isset($data[$key])) {
                            $data[$key] += $inv->amount_paid / 100;
                        }
                    }
                } catch (\Throwable $e) {
                    Log::warning('StripeAnalyticsService: monthlyRevenueChart failed', ['error' => $e->getMessage()]);
                    // Fallback to local DB
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
            if (isset($data[$key])) {
                $data[$key] += (float) $row->total;
            }
        }

        return $data;
    }

    // ── Recent payments from Stripe ───────────────────────────────────────────

    public function recentPayments(int $limit = 20): array
    {
        return Cache::remember("stripe_analytics.recent_payments_{$limit}", self::TTL, function () use ($limit) {
            if (!$this->isConfigured()) {
                return $this->recentPaymentsFromDb($limit);
            }

            try {
                $stripe   = $this->client();
                $invoices = $stripe->invoices->all([
                    'status' => 'paid',
                    'limit'  => $limit,
                    'expand' => ['data.customer'],
                ]);

                return collect($invoices->data)->map(fn ($inv) => [
                    'id'          => $inv->id,
                    'amount'      => $inv->amount_paid / 100,
                    'currency'    => strtoupper($inv->currency),
                    'status'      => 'paid',
                    'customer'    => $inv->customer?->email ?? $inv->customer_email ?? '—',
                    'description' => $inv->lines?->data[0]?->description ?? $inv->description ?? 'Abonnement',
                    'date'        => date('d/m/Y', $inv->created),
                    'stripe_url'  => $inv->hosted_invoice_url ?? null,
                ])->toArray();
            } catch (\Throwable $e) {
                Log::warning('StripeAnalyticsService: recentPayments failed', ['error' => $e->getMessage()]);
                return $this->recentPaymentsFromDb($limit);
            }
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

    // ── New vs cancelled subscriptions (last 30 days) ────────────────────────

    public function newVsCancelled(): array
    {
        return Cache::remember('stripe_analytics.new_vs_cancelled', self::TTL, function () {
            $since = now()->subDays(30);
            return [
                'new'       => Subscription::where('created_at', '>=', $since)->count(),
                'cancelled' => Subscription::where('status', 'canceled')
                                ->where('updated_at', '>=', $since)->count(),
            ];
        });
    }

    public function clearCache(): void
    {
        $keys = [
            'stripe_analytics.mrr', 'stripe_analytics.churn', 'stripe_analytics.ltv',
            'stripe_analytics.new_vs_cancelled',
            'stripe_analytics.monthly_revenue_12',
            'stripe_analytics.recent_payments_20',
        ];
        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}
