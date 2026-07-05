<?php

namespace App\Services;

use App\Models\Connection;
use App\Models\ConsulRequest;
use App\Models\Event;
use App\Models\Group;
use App\Models\Lead;
use App\Models\Notification;
use App\Models\Plan;
use App\Models\Profile;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    private const TTL = 300; // 5 min cache

    private static array $fr = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];

    // ─── Overview KPIs ────────────────────────────────────────────────────────

    public function overviewKpis(): array
    {
        return Cache::remember('analytics.overview_kpis', self::TTL, function () {
            $now        = now();
            $weekStart  = $now->copy()->startOfWeek();
            $monthStart = $now->copy()->startOfMonth();

            $totalUsers    = User::where('role', 'user')->count();
            $activeUsers   = $this->countActiveUsers();
            $newToday      = User::where('role', 'user')->whereDate('created_at', today())->count();
            $newWeek       = User::where('role', 'user')->where('created_at', '>=', $weekStart)->count();
            $newMonth      = User::where('role', 'user')->where('created_at', '>=', $monthStart)->count();
            $verified      = User::where('role', 'user')->whereNotNull('email_verified_at')->count();
            $unverified    = $totalUsers - $verified;

            $totalLeads    = Lead::count();
            $accepted      = Lead::where('status', 'accepted')->count();
            $rejected      = Lead::where('status', 'rejected')->count();
            $expired       = Lead::where('status', 'expired')->count();
            $converted     = Lead::where('status', 'converted')->count();
            $convRate      = $totalLeads > 0 ? round($accepted / $totalLeads * 100, 1) : 0;

            $totalInv      = Connection::count();
            $totalConn     = Connection::where('status', 'accepted')->count();
            $rejectedConn  = Connection::where('status', 'rejected')->count();
            $pendingConn   = Connection::where('status', 'pending')->count();
            $connRate      = $totalInv > 0 ? round($totalConn / $totalInv * 100, 1) : 0;

            $totalEvents   = Event::count();
            $upcoming      = Event::where('starts_at', '>', $now)->count();
            $completed     = Event::where('ends_at', '<', $now)->count();

            $totalGroups   = Group::count();

            $activeSubs    = Subscription::where('status', 'active')->count();
            $premiumSubs   = Subscription::where('status', 'active')
                ->whereHas('plan', fn ($q) => $q->where('price', '>', 0))->count();
            $freeSubs      = $activeSubs - $premiumSubs;

            $ambassadors   = User::where('ambassador_status', 'approved')->count();
            $consuls       = User::where('consul_status', 'approved')->count();
            $pendingAmb    = ConsulRequest::where('status', 'pending')->count();

            $revenue = DB::table('subscriptions')
                ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
                ->where('subscriptions.status', 'active')
                ->sum('plans.price');

            return compact(
                'totalUsers', 'activeUsers', 'newToday', 'newWeek', 'newMonth',
                'verified', 'unverified',
                'totalLeads', 'accepted', 'rejected', 'expired', 'converted', 'convRate',
                'totalInv', 'totalConn', 'connRate', 'rejectedConn', 'pendingConn',
                'totalEvents', 'upcoming', 'completed',
                'totalGroups',
                'activeSubs', 'premiumSubs', 'freeSubs',
                'ambassadors', 'consuls', 'pendingAmb',
                'revenue'
            );
        });
    }

    private function countActiveUsers(): int
    {
        $since = now()->subDays(30);
        return DB::table('users')
            ->where('role', 'user')
            ->where(function ($q) use ($since) {
                $q->whereExists(fn ($s) => $s->from('leads')
                    ->whereColumn('leads.sender_id', 'users.id')
                    ->where('leads.created_at', '>=', $since)
                )->orWhereExists(fn ($s) => $s->from('connections')
                    ->whereColumn('connections.sender_id', 'users.id')
                    ->where('connections.created_at', '>=', $since)
                )->orWhereExists(fn ($s) => $s->from('events')
                    ->whereColumn('events.created_by', 'users.id')
                    ->where('events.created_at', '>=', $since)
                );
            })
            ->count();
    }

    // ─── User Charts ──────────────────────────────────────────────────────────

    public function userGrowthChart(int $months = 12): array
    {
        return Cache::remember("analytics.user_growth_{$months}", self::TTL, function () use ($months) {
            $counts = DB::table('users')
                ->where('role', 'user')
                ->where('created_at', '>=', now()->subMonths($months)->startOfMonth())
                ->select(DB::raw('YEAR(created_at) as yr'), DB::raw('MONTH(created_at) as mo'), DB::raw('COUNT(*) as cnt'))
                ->groupBy('yr', 'mo')
                ->get()
                ->keyBy(fn ($r) => $r->yr . '-' . str_pad($r->mo, 2, '0', STR_PAD_LEFT));

            $base = DB::table('users')
                ->where('role', 'user')
                ->where('created_at', '<', now()->subMonths($months)->startOfMonth())
                ->count();

            $labels = $monthly = $cumulative = [];
            $running = $base;
            for ($i = $months - 1; $i >= 0; $i--) {
                $d       = now()->subMonths($i)->startOfMonth();
                $key     = $d->format('Y-m');
                $cnt     = $counts->get($key)?->cnt ?? 0;
                $running += $cnt;
                $labels[]     = self::$fr[$d->month - 1] . ' ' . $d->format('y');
                $monthly[]    = $cnt;
                $cumulative[] = $running;
            }
            return ['labels' => $labels, 'monthly' => $monthly, 'cumulative' => $cumulative];
        });
    }

    public function usersByRegion(int $limit = 15): array
    {
        return Cache::remember("analytics.users_by_region_{$limit}", self::TTL, fn () =>
            DB::table('cities')
                ->join('users', 'users.city_id', '=', 'cities.id')
                ->where('users.role', 'user')
                ->select('cities.name', DB::raw('count(users.id) as count'))
                ->groupBy('cities.id', 'cities.name')
                ->orderByDesc('count')
                ->limit($limit)
                ->get()->toArray()
        );
    }

    public function profileCompletion(): array
    {
        return Cache::remember('analytics.profile_completion', self::TTL, function () {
            $total = User::where('role', 'user')->count();
            if ($total === 0) {
                return ['average' => 0, 'total' => 0, 'details' => []];
            }

            $withAvatar    = Profile::whereNotNull('avatar')->where('avatar', '!=', '')->count();
            $withBio       = Profile::whereNotNull('bio')->where('bio', '!=', '')->count();
            $withInterests = DB::table('user_interests')->distinct('user_id')->count('user_id');
            $withCompany   = User::where('role', 'user')->whereNotNull('company_id')->count();

            $average = round(($withAvatar + $withBio + $withInterests + $withCompany) / ($total * 4) * 100, 1);

            return [
                'average' => $average,
                'total'   => $total,
                'details' => [
                    ['label' => 'Photo de profil',    'with' => $withAvatar,    'missing' => $total - $withAvatar,    'pct' => round($withAvatar / $total * 100)],
                    ['label' => 'Biographie',          'with' => $withBio,       'missing' => $total - $withBio,       'pct' => round($withBio / $total * 100)],
                    ['label' => "Centres d'intérêt",   'with' => $withInterests, 'missing' => $total - $withInterests, 'pct' => round($withInterests / $total * 100)],
                    ['label' => 'Entreprise',           'with' => $withCompany,   'missing' => $total - $withCompany,   'pct' => round($withCompany / $total * 100)],
                ],
            ];
        });
    }

    // ─── Leads Charts ─────────────────────────────────────────────────────────

    public function leadsChart(int $months = 12): array
    {
        return Cache::remember("analytics.leads_chart_{$months}", self::TTL, function () use ($months) {
            $rows = DB::table('leads')
                ->where('created_at', '>=', now()->subMonths($months)->startOfMonth())
                ->select(DB::raw('YEAR(created_at) as yr'), DB::raw('MONTH(created_at) as mo'), 'status', DB::raw('COUNT(*) as cnt'))
                ->groupBy('yr', 'mo', 'status')
                ->get();

            $indexed = [];
            foreach ($rows as $r) {
                $key = $r->yr . '-' . str_pad($r->mo, 2, '0', STR_PAD_LEFT);
                $indexed[$key][$r->status] = $r->cnt;
            }

            $labels = $sent = $acc = $rej = [];
            for ($i = $months - 1; $i >= 0; $i--) {
                $d   = now()->subMonths($i)->startOfMonth();
                $key = $d->format('Y-m');
                $labels[] = self::$fr[$d->month - 1] . ' ' . $d->format('y');
                $sent[]   = array_sum($indexed[$key] ?? []);
                $acc[]    = $indexed[$key]['accepted'] ?? 0;
                $rej[]    = $indexed[$key]['rejected'] ?? 0;
            }
            return ['labels' => $labels, 'sent' => $sent, 'accepted' => $acc, 'rejected' => $rej];
        });
    }

    public function topLeadGenerators(int $limit = 10): array
    {
        return Cache::remember("analytics.top_generators_{$limit}", self::TTL, fn () =>
            DB::table('leads')
                ->join('users', 'leads.sender_id', '=', 'users.id')
                ->select(
                    'users.id',
                    DB::raw("CONCAT(users.first_name, ' ', users.last_name) as name"),
                    'users.email',
                    DB::raw('COUNT(*) as total'),
                    DB::raw('SUM(CASE WHEN leads.status="accepted" THEN 1 ELSE 0 END) as accepted'),
                    DB::raw('SUM(CASE WHEN leads.status="rejected" THEN 1 ELSE 0 END) as rejected')
                )
                ->groupBy('users.id', 'users.first_name', 'users.last_name', 'users.email')
                ->orderByDesc('total')
                ->limit($limit)
                ->get()->toArray()
        );
    }

    // ─── Connections Chart ────────────────────────────────────────────────────

    public function connectionsChart(int $months = 12): array
    {
        return Cache::remember("analytics.connections_chart_{$months}", self::TTL, function () use ($months) {
            $rows = DB::table('connections')
                ->where('created_at', '>=', now()->subMonths($months)->startOfMonth())
                ->select(DB::raw('YEAR(created_at) as yr'), DB::raw('MONTH(created_at) as mo'), 'status', DB::raw('COUNT(*) as cnt'))
                ->groupBy('yr', 'mo', 'status')
                ->get();

            $indexed = [];
            foreach ($rows as $r) {
                $key = $r->yr . '-' . str_pad($r->mo, 2, '0', STR_PAD_LEFT);
                $indexed[$key][$r->status] = $r->cnt;
            }

            $labels = $sent = $acc = [];
            for ($i = $months - 1; $i >= 0; $i--) {
                $d   = now()->subMonths($i)->startOfMonth();
                $key = $d->format('Y-m');
                $labels[] = self::$fr[$d->month - 1] . ' ' . $d->format('y');
                $sent[]   = array_sum($indexed[$key] ?? []);
                $acc[]    = $indexed[$key]['accepted'] ?? 0;
            }
            return ['labels' => $labels, 'sent' => $sent, 'accepted' => $acc];
        });
    }

    // ─── Events Charts ────────────────────────────────────────────────────────

    public function eventsChart(int $months = 12): array
    {
        return Cache::remember("analytics.events_chart_{$months}", self::TTL, function () use ($months) {
            $rows = DB::table('events')
                ->where('created_at', '>=', now()->subMonths($months)->startOfMonth())
                ->select(
                    DB::raw('YEAR(created_at) as yr'), DB::raw('MONTH(created_at) as mo'),
                    DB::raw('COUNT(*) as cnt'),
                    DB::raw('SUM(COALESCE(attendees_count,0)) as participation')
                )
                ->groupBy('yr', 'mo')
                ->get()
                ->keyBy(fn ($r) => $r->yr . '-' . str_pad($r->mo, 2, '0', STR_PAD_LEFT));

            $labels = $created = $participation = [];
            for ($i = $months - 1; $i >= 0; $i--) {
                $d   = now()->subMonths($i)->startOfMonth();
                $key = $d->format('Y-m');
                $row = $rows->get($key);
                $labels[]       = self::$fr[$d->month - 1] . ' ' . $d->format('y');
                $created[]      = $row?->cnt ?? 0;
                $participation[] = $row?->participation ?? 0;
            }
            return ['labels' => $labels, 'created' => $created, 'participation' => $participation];
        });
    }

    public function topEvents(int $limit = 10): array
    {
        return Cache::remember("analytics.top_events_{$limit}", self::TTL, fn () =>
            DB::table('events')
                ->leftJoin('users', 'events.created_by', '=', 'users.id')
                ->leftJoin('cities', 'events.city_id', '=', 'cities.id')
                ->select(
                    'events.id', 'events.title', 'events.type', 'events.starts_at',
                    DB::raw('COALESCE(events.attendees_count,0) as attendees_count'),
                    DB::raw("CONCAT(users.first_name,' ',users.last_name) as creator"),
                    'cities.name as city_name'
                )
                ->orderByDesc('events.attendees_count')
                ->limit($limit)
                ->get()->toArray()
        );
    }

    // ─── Subscriptions ────────────────────────────────────────────────────────

    public function subscriptionDistribution(): array
    {
        return Cache::remember('analytics.sub_distribution', self::TTL, fn () =>
            DB::table('subscriptions')
                ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
                ->where('subscriptions.status', 'active')
                ->select('plans.id', 'plans.label', 'plans.name', DB::raw('count(*) as count'))
                ->groupBy('plans.id', 'plans.label', 'plans.name')
                ->orderByDesc('count')
                ->get()->toArray()
        );
    }

    public function subscriptionGrowthChart(int $months = 12): array
    {
        return Cache::remember("analytics.sub_growth_{$months}", self::TTL, function () use ($months) {
            $rows = DB::table('subscriptions')
                ->where('status', 'active')
                ->where('created_at', '>=', now()->subMonths($months)->startOfMonth())
                ->select(DB::raw('YEAR(created_at) as yr'), DB::raw('MONTH(created_at) as mo'), DB::raw('COUNT(*) as cnt'))
                ->groupBy('yr', 'mo')
                ->get()
                ->keyBy(fn ($r) => $r->yr . '-' . str_pad($r->mo, 2, '0', STR_PAD_LEFT));

            $labels = $data = [];
            for ($i = $months - 1; $i >= 0; $i--) {
                $d   = now()->subMonths($i)->startOfMonth();
                $key = $d->format('Y-m');
                $labels[] = self::$fr[$d->month - 1] . ' ' . $d->format('y');
                $data[]   = $rows->get($key)?->cnt ?? 0;
            }
            return ['labels' => $labels, 'data' => $data];
        });
    }

    // ─── Ambassadors ──────────────────────────────────────────────────────────

    public function ambassadorStats(): array
    {
        return Cache::remember('analytics.ambassador_stats', self::TTL, function () {
            $pending  = ConsulRequest::where('status', 'pending')->count();
            $approved = User::where('ambassador_status', 'approved')->count();
            $rejected = ConsulRequest::where('status', 'rejected')->count();
            $consuls  = User::where('consul_status', 'approved')->count();
            return compact('pending', 'approved', 'rejected', 'consuls');
        });
    }

    public function topAmbassadors(int $limit = 10): array
    {
        return Cache::remember("analytics.top_ambassadors_{$limit}", self::TTL, fn () =>
            DB::table('users')
                ->leftJoin('cities', 'users.city_id', '=', 'cities.id')
                ->leftJoin(
                    DB::raw('(SELECT sender_id, COUNT(*) as leads_sent FROM leads GROUP BY sender_id) as l'),
                    'l.sender_id', '=', 'users.id'
                )
                ->leftJoin(
                    DB::raw('(SELECT created_by, COUNT(*) as ev_cnt FROM events GROUP BY created_by) as e'),
                    'e.created_by', '=', 'users.id'
                )
                ->where('users.ambassador_status', 'approved')
                ->select(
                    'users.id',
                    DB::raw("CONCAT(users.first_name,' ',users.last_name) as name"),
                    'users.email',
                    'users.points_balance',
                    'users.badge_level',
                    'cities.name as city_name',
                    DB::raw('COALESCE(l.leads_sent, 0) as leads_sent'),
                    DB::raw('COALESCE(e.ev_cnt, 0) as events_created')
                )
                ->orderByDesc('users.points_balance')
                ->limit($limit)
                ->get()->toArray()
        );
    }

    // ─── Regional ─────────────────────────────────────────────────────────────

    public function regionalStats(): array
    {
        return Cache::remember('analytics.regional_stats', self::TTL, fn () =>
            DB::table('cities')
                ->leftJoin(
                    DB::raw('(SELECT city_id, COUNT(*) as cnt FROM users WHERE role="user" AND city_id IS NOT NULL GROUP BY city_id) as uc'),
                    'uc.city_id', '=', 'cities.id'
                )
                ->leftJoin(
                    DB::raw('(SELECT city_id, COUNT(*) as cnt FROM users WHERE ambassador_status="approved" AND city_id IS NOT NULL GROUP BY city_id) as ac'),
                    'ac.city_id', '=', 'cities.id'
                )
                ->leftJoin(
                    DB::raw('(SELECT city_id, COUNT(*) as cnt FROM events WHERE city_id IS NOT NULL GROUP BY city_id) as ec'),
                    'ec.city_id', '=', 'cities.id'
                )
                ->leftJoin(
                    DB::raw('(SELECT u2.city_id, COUNT(*) as cnt FROM leads JOIN users u2 ON leads.sender_id=u2.id WHERE u2.city_id IS NOT NULL GROUP BY u2.city_id) as lc'),
                    'lc.city_id', '=', 'cities.id'
                )
                ->where('cities.is_active', true)
                ->select(
                    'cities.id', 'cities.name',
                    DB::raw('COALESCE(uc.cnt,0) as users_count'),
                    DB::raw('COALESCE(ac.cnt,0) as ambassadors_count'),
                    DB::raw('COALESCE(ec.cnt,0) as events_count'),
                    DB::raw('COALESCE(lc.cnt,0) as leads_count')
                )
                ->having(DB::raw('COALESCE(uc.cnt,0)'), '>', 0)
                ->orderByDesc('users_count')
                ->limit(20)
                ->get()->toArray()
        );
    }

    // ─── Today / Notifications / System ──────────────────────────────────────

    public function todayActivity(): array
    {
        return [
            'new_users'       => User::where('role', 'user')->whereDate('created_at', today())->count(),
            'new_leads'       => Lead::whereDate('created_at', today())->count(),
            'new_connections' => Connection::whereDate('created_at', today())->count(),
            'new_events'      => Event::whereDate('created_at', today())->count(),
            'new_groups'      => Group::whereDate('created_at', today())->count(),
        ];
    }

    public function notificationStats(): array
    {
        return Cache::remember('analytics.notifications', self::TTL, function () {
            $total   = Notification::count();
            $read    = Notification::where('is_read', true)->count();
            $openRate = $total > 0 ? round($read / $total * 100, 1) : 0;
            return ['total' => $total, 'read' => $read, 'unread' => $total - $read, 'open_rate' => $openRate];
        });
    }

    public function systemHealth(): array
    {
        $checks = [];

        $checks['web_server'] = ['status' => 'ok', 'label' => 'Serveur Web', 'detail' => 'PHP ' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION];

        try {
            DB::select('SELECT 1');
            $checks['database'] = ['status' => 'ok', 'label' => 'Base de données', 'detail' => 'Connexion MySQL établie'];
        } catch (\Throwable) {
            $checks['database'] = ['status' => 'error', 'label' => 'Base de données', 'detail' => 'Erreur de connexion'];
        }

        try {
            $free    = disk_free_space(storage_path());
            $total   = disk_total_space(storage_path());
            $used    = round((1 - $free / $total) * 100, 1);
            $freeGb  = round($free / 1073741824, 1);
            $checks['storage'] = [
                'status' => $used > 90 ? 'error' : ($used > 70 ? 'warning' : 'ok'),
                'label'  => 'Stockage',
                'detail' => "{$used}% utilisé — {$freeGb} Go libres",
            ];
        } catch (\Throwable) {
            $checks['storage'] = ['status' => 'warning', 'label' => 'Stockage', 'detail' => 'Info indisponible'];
        }

        $smtp = config('mail.mailers.smtp.host');
        $checks['email'] = [
            'status' => ($smtp && $smtp !== 'mailpit' && $smtp !== '127.0.0.1') ? 'ok' : 'warning',
            'label'  => 'Service Email',
            'detail' => $smtp ?: 'Non configuré',
        ];

        $stripe = config('services.stripe.secret');
        $checks['stripe'] = [
            'status' => $stripe ? 'ok' : 'warning',
            'label'  => 'Stripe',
            'detail' => $stripe ? 'Clé API configurée' : 'Clé API manquante',
        ];

        try {
            cache()->put('_analytics_health_', 1, 5);
            $checks['cache'] = [
                'status' => cache()->get('_analytics_health_') ? 'ok' : 'warning',
                'label'  => 'Cache',
                'detail' => ucfirst(config('cache.default', 'file')),
            ];
        } catch (\Throwable) {
            $checks['cache'] = ['status' => 'error', 'label' => 'Cache', 'detail' => 'Erreur'];
        }

        try {
            $failed = DB::table('failed_jobs')->count();
            $checks['queue'] = [
                'status' => $failed > 10 ? 'error' : ($failed > 0 ? 'warning' : 'ok'),
                'label'  => 'File de tâches',
                'detail' => $failed > 0 ? "{$failed} tâche(s) échouée(s)" : 'Aucune erreur',
            ];
        } catch (\Throwable) {
            $checks['queue'] = ['status' => 'warning', 'label' => 'File de tâches', 'detail' => 'Indisponible'];
        }

        $checks['ssl'] = [
            'status' => request()->isSecure() ? 'ok' : 'warning',
            'label'  => 'Certificat SSL',
            'detail' => request()->isSecure() ? 'HTTPS actif' : 'HTTP (environnement local)',
        ];

        $firebase = config('services.firebase.api_key', null);
        $checks['firebase'] = [
            'status' => $firebase ? 'ok' : 'warning',
            'label'  => 'Firebase',
            'detail' => $firebase ? 'Configuré' : 'Non configuré',
        ];

        return $checks;
    }

    public function flushCache(): void
    {
        foreach ([
            'analytics.overview_kpis', 'analytics.profile_completion',
            'analytics.sub_distribution', 'analytics.ambassador_stats',
            'analytics.regional_stats', 'analytics.notifications',
            'analytics.user_growth_12', 'analytics.leads_chart_12',
            'analytics.connections_chart_12', 'analytics.events_chart_12',
            'analytics.sub_growth_12', 'analytics.top_generators_10',
            'analytics.top_events_10', 'analytics.top_ambassadors_10',
            'analytics.users_by_region_15',
        ] as $key) {
            Cache::forget($key);
        }
    }
}
