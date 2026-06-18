<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Connection;
use App\Models\Country;
use App\Models\Group;
use App\Models\Lead;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $now          = now();
        $thisMonth    = $now->copy()->startOfMonth();
        $lastMonth    = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        // ── Active filters ───────────────────────────────────────────────────
        $cityId      = $request->integer('city_id') ?: null;
        $planId      = $request->integer('plan_id') ?: null;
        $countryId   = $request->integer('country_id') ?: null;
        $groupId     = $request->integer('group_id') ?: null;
        $leadsPeriod = $request->input('leads_period');

        $leadsFrom = match ($leadsPeriod) {
            'today'   => $now->copy()->startOfDay(),
            'week'    => $now->copy()->startOfWeek(),
            'month'   => $now->copy()->startOfMonth(),
            'quarter' => $now->copy()->startOfQuarter(),
            default   => null,
        };

        // ── Base closures for reusable filtering ─────────────────────────────
        $filterUser = function ($query) use ($cityId, $planId, $countryId, $groupId) {
            if ($cityId)    $query->where('city_id', $cityId);
            if ($countryId) $query->whereHas('city', fn ($q) => $q->where('country_id', $countryId));
            if ($groupId)   $query->whereHas('groups', fn ($q) => $q->where('groups.id', $groupId));
            if ($planId)    $query->whereHas('subscription', fn ($q) =>
                $q->where('plan_id', $planId)->where('status', 'active')
            );
        };

        $filterLead = function ($query) use ($cityId, $planId, $countryId, $groupId, $leadsFrom, $now) {
            if ($cityId)    $query->whereHas('sender', fn ($q) => $q->where('city_id', $cityId));
            if ($countryId) $query->whereHas('sender.city', fn ($q) => $q->where('country_id', $countryId));
            if ($groupId)   $query->whereHas('sender.groups', fn ($q) => $q->where('groups.id', $groupId));
            if ($planId)    $query->whereHas('sender.subscription', fn ($q) =>
                $q->where('plan_id', $planId)->where('status', 'active')
            );
            if ($leadsFrom) $query->whereBetween('created_at', [$leadsFrom, $now]);
        };

        $filterConn = function ($query) use ($cityId, $planId, $countryId, $groupId) {
            $query->where('status', 'accepted');
            if ($cityId)    $query->whereHas('sender', fn ($q) => $q->where('city_id', $cityId));
            if ($countryId) $query->whereHas('sender.city', fn ($q) => $q->where('country_id', $countryId));
            if ($groupId)   $query->whereHas('sender.groups', fn ($q) => $q->where('groups.id', $groupId));
            if ($planId)    $query->whereHas('sender.subscription', fn ($q) =>
                $q->where('plan_id', $planId)->where('status', 'active')
            );
        };

        $filterSub = function ($query) use ($cityId, $planId, $countryId, $groupId) {
            $query->where('status', 'active');
            if ($planId)    $query->where('plan_id', $planId);
            if ($cityId)    $query->whereHas('user', fn ($q) => $q->where('city_id', $cityId));
            if ($countryId) $query->whereHas('user.city', fn ($q) => $q->where('country_id', $countryId));
            if ($groupId)   $query->whereHas('user.groups', fn ($q) => $q->where('groups.id', $groupId));
        };

        // ── KPIs ─────────────────────────────────────────────────────────────
        $totalMembers     = User::where('role', 'user')->tap($filterUser)->count();
        $membersThisMonth = User::where('role', 'user')->tap($filterUser)->whereBetween('created_at', [$thisMonth, $now])->count();
        $membersLastMonth = User::where('role', 'user')->tap($filterUser)->whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();

        $totalLeads     = Lead::tap($filterLead)->count();
        $leadsThisMonth = Lead::tap($filterLead)->whereBetween('created_at', [$thisMonth, $now])->count();
        $leadsLastMonth = Lead::tap($filterLead)->whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();

        $totalConnections = Connection::tap($filterConn)->count();
        $connThisMonth    = Connection::tap($filterConn)->whereBetween('created_at', [$thisMonth, $now])->count();
        $connLastMonth    = Connection::tap($filterConn)->whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();

        $totalSubscribers = Subscription::tap($filterSub)->count();
        $subsThisMonth    = Subscription::tap($filterSub)->whereBetween('created_at', [$thisMonth, $now])->count();
        $subsLastMonth    = Subscription::tap($filterSub)->whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();

        $totalEvents    = DB::table('events')->count();
        $upcomingEvents = DB::table('events')->where('starts_at', '>', $now)->count();
        $totalGroups    = DB::table('groups')->count();

        $totalAmbassadors   = User::where('ambassador_status', 'approved')->tap($filterUser)->count();
        $pendingAmbassadors = User::where('ambassador_status', 'pending')->count();

        $pendingVideos   = DB::table('profiles')->whereNotNull('presentation_video')->where('presentation_video_status', 'pending')->count();
        $fraudLeads      = Lead::tap($filterLead)->where('fraud_reported', true)->count();
        $unverifiedUsers = User::where('role', 'user')->whereNull('email_verified_at')->tap($filterUser)->count();

        $revenueQuery = DB::table('subscriptions')
            ->where('subscriptions.status', 'active')
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->join('users', 'subscriptions.user_id', '=', 'users.id');
        if ($planId)    $revenueQuery->where('subscriptions.plan_id', $planId);
        if ($cityId)    $revenueQuery->where('users.city_id', $cityId);
        if ($countryId) $revenueQuery->join('cities', 'users.city_id', '=', 'cities.id')->where('cities.country_id', $countryId);
        if ($groupId)   $revenueQuery->whereExists(fn ($q) => $q->from('group_user')->whereColumn('group_user.user_id', 'users.id')->where('group_user.group_id', $groupId));
        $monthlyRevenue = $revenueQuery->sum('plans.price');

        // ── Charts : 6 derniers mois ──────────────────────────────────────────
        $frMonths    = ['Jan','Fév','Mar','Avr','Mai','Juin','Juil','Aoû','Sep','Oct','Nov','Déc'];
        $chartMonths = collect(range(5, 0))->map(fn ($i) => $now->copy()->subMonths($i)->startOfMonth());

        $chartLabels        = $chartMonths->map(fn ($m) => $frMonths[$m->month - 1] . ' ' . $m->format('y'))->values()->toArray();
        $chartRegistrations = $chartMonths->map(fn ($m) =>
            User::where('role', 'user')->tap($filterUser)->whereBetween('created_at', [$m, $m->copy()->endOfMonth()])->count()
        )->values()->toArray();
        $chartLeads = $chartMonths->map(fn ($m) =>
            Lead::tap($filterLead)->whereBetween('created_at', [$m, $m->copy()->endOfMonth()])->count()
        )->values()->toArray();
        $chartConnections = $chartMonths->map(fn ($m) =>
            Connection::tap($filterConn)->whereBetween('created_at', [$m, $m->copy()->endOfMonth()])->count()
        )->values()->toArray();

        // ── Plan distribution (donut) ─────────────────────────────────────────
        $planDistribution = Plan::withCount(['activeSubscriptions' => function ($q) use ($cityId, $countryId, $groupId) {
            if ($cityId)    $q->whereHas('user', fn ($u) => $u->where('city_id', $cityId));
            if ($countryId) $q->whereHas('user.city', fn ($u) => $u->where('country_id', $countryId));
            if ($groupId)   $q->whereHas('user.groups', fn ($u) => $u->where('groups.id', $groupId));
        }])->orderBy('sort_order')->get(['id', 'name', 'label']);

        // ── Lists ─────────────────────────────────────────────────────────────
        $pendingList = User::with(['region', 'profile', 'company'])
            ->where('ambassador_status', 'pending')
            ->orderBy('ambassador_requested_at')
            ->limit(6)
            ->get(['id', 'first_name', 'last_name', 'email', 'region_id', 'company_id', 'points_balance', 'badge_level', 'ambassador_requested_at']);

        $recentUsers = User::with('subscription.plan')->where('role', 'user')->tap($filterUser)->latest()->limit(8)
            ->get(['id', 'first_name', 'last_name', 'email', 'created_at', 'email_verified_at']);

        // ── Filter options ────────────────────────────────────────────────────
        $cities    = City::orderBy('name')->get(['id', 'name']);
        $plans     = Plan::orderBy('sort_order')->get(['id', 'label']);
        $countries = Country::orderBy('name')->get(['id', 'name']);
        $groups    = Group::orderBy('name')->get(['id', 'name']);

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
            'cities'             => $cities,
            'plans'              => $plans,
            'countries'          => $countries,
            'groups'             => $groups,
            'activeFilters'      => [
                'city_id'      => $cityId,
                'plan_id'      => $planId,
                'country_id'   => $countryId,
                'group_id'     => $groupId,
                'leads_period' => $leadsPeriod,
            ],
        ]);
    }

    private function growth(int $current, int $previous): float
    {
        if ($previous === 0) return $current > 0 ? 100.0 : 0.0;
        return round((($current - $previous) / $previous) * 100, 1);
    }
}
