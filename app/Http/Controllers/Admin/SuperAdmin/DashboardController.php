<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Connection;
use App\Models\Lead;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $now          = now();
        $thisMonth    = $now->copy()->startOfMonth();
        $lastMonth    = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // ── KPIs ────────────────────────────────────────────────────────────
        $totalMembers     = User::where('role', 'user')->count();
        $membersThisMonth = User::where('role', 'user')->whereBetween('created_at', [$thisMonth, $now])->count();
        $membersLastMonth = User::where('role', 'user')->whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();

        $totalLeads     = Lead::count();
        $leadsThisMonth = Lead::whereBetween('created_at', [$thisMonth, $now])->count();
        $leadsLastMonth = Lead::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();

        $totalConnections = Connection::where('status', 'accepted')->count();
        $connThisMonth    = Connection::where('status', 'accepted')->whereBetween('created_at', [$thisMonth, $now])->count();
        $connLastMonth    = Connection::where('status', 'accepted')->whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();

        $totalSubscribers = Subscription::where('status', 'active')->count();
        $subsThisMonth    = Subscription::where('status', 'active')->whereBetween('created_at', [$thisMonth, $now])->count();
        $subsLastMonth    = Subscription::where('status', 'active')->whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();

        $totalEvents    = DB::table('events')->count();
        $upcomingEvents = DB::table('events')->where('starts_at', '>', $now)->count();
        $totalGroups    = DB::table('groups')->count();

        $totalAmbassadors   = User::where('ambassador_status', 'approved')->count();
        $pendingAmbassadors = User::where('ambassador_status', 'pending')->count();

        $pendingVideos   = DB::table('profiles')->whereNotNull('presentation_video')->where('presentation_video_status', 'pending')->count();
        $fraudLeads      = DB::table('leads')->where('fraud_reported', true)->count();
        $unverifiedUsers = User::where('role', 'user')->whereNull('email_verified_at')->count();

        $monthlyRevenue = DB::table('subscriptions')
            ->where('subscriptions.status', 'active')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->sum('plans.price');

        // ── Charts : 6 derniers mois ─────────────────────────────────────────
        $frMonths    = ['Jan','Fév','Mar','Avr','Mai','Juin','Juil','Aoû','Sep','Oct','Nov','Déc'];
        $chartMonths = collect(range(5, 0))->map(fn($i) => $now->copy()->subMonths($i)->startOfMonth());

        $chartLabels        = $chartMonths->map(fn($m) => $frMonths[$m->month - 1] . ' ' . $m->format('y'))->values()->toArray();
        $chartRegistrations = $chartMonths->map(fn($m) => User::where('role', 'user')->whereBetween('created_at', [$m, $m->copy()->endOfMonth()])->count())->values()->toArray();
        $chartLeads         = $chartMonths->map(fn($m) => Lead::whereBetween('created_at', [$m, $m->copy()->endOfMonth()])->count())->values()->toArray();
        $chartConnections   = $chartMonths->map(fn($m) => Connection::where('status', 'accepted')->whereBetween('created_at', [$m, $m->copy()->endOfMonth()])->count())->values()->toArray();

        // ── Plan distribution (donut) ────────────────────────────────────────
        $planDistribution = Plan::withCount('activeSubscriptions')->orderBy('sort_order')->get(['id', 'name', 'label']);

        // ── Ambassador pending list ──────────────────────────────────────────
        $pendingList = User::with(['region', 'profile', 'company'])
            ->where('ambassador_status', 'pending')
            ->orderBy('ambassador_requested_at')
            ->limit(6)
            ->get(['id', 'first_name', 'last_name', 'email', 'region_id', 'company_id', 'points_balance', 'badge_level', 'ambassador_requested_at']);

        // ── Recent users ─────────────────────────────────────────────────────
        $recentUsers = User::with('subscription.plan')
            ->where('role', 'user')
            ->latest()
            ->limit(8)
            ->get(['id', 'first_name', 'last_name', 'email', 'created_at', 'email_verified_at']);

        return view('admin.super_admin.dashboard.index', [
            'stats' => [
                'total_members'       => $totalMembers,
                'member_growth'       => $this->growth($membersThisMonth, $membersLastMonth),
                'total_leads'         => $totalLeads,
                'leads_growth'        => $this->growth($leadsThisMonth, $leadsLastMonth),
                'total_connections'   => $totalConnections,
                'conn_growth'         => $this->growth($connThisMonth, $connLastMonth),
                'total_subscribers'   => $totalSubscribers,
                'subs_growth'         => $this->growth($subsThisMonth, $subsLastMonth),
                'total_events'        => $totalEvents,
                'upcoming_events'     => $upcomingEvents,
                'total_groups'        => $totalGroups,
                'total_ambassadors'   => $totalAmbassadors,
                'pending_ambassadors' => $pendingAmbassadors,
                'pending_videos'      => $pendingVideos,
                'fraud_leads'         => $fraudLeads,
                'unverified_users'    => $unverifiedUsers,
                'monthly_revenue'     => $monthlyRevenue,
            ],
            'chartLabels'        => $chartLabels,
            'chartRegistrations' => $chartRegistrations,
            'chartLeads'         => $chartLeads,
            'chartConnections'   => $chartConnections,
            'planDistribution'   => $planDistribution,
            'pendingList'        => $pendingList,
            'recentUsers'        => $recentUsers,
        ]);
    }

    private function growth(int $current, int $previous): float
    {
        if ($previous === 0) return $current > 0 ? 100.0 : 0.0;
        return round((($current - $previous) / $previous) * 100, 1);
    }
}
