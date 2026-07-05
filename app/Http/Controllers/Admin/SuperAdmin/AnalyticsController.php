<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Connection;
use App\Models\ConsulRequest;
use App\Models\Event;
use App\Models\Lead;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Services\AnalyticsService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __construct(private AnalyticsService $analytics) {}

    public function overview(): View
    {
        return view('admin.super_admin.analytics.overview', [
            'kpis'             => $this->analytics->overviewKpis(),
            'userGrowth'       => $this->analytics->userGrowthChart(12),
            'leadsChart'       => $this->analytics->leadsChart(6),
            'subDistribution'  => $this->analytics->subscriptionDistribution(),
            'todayActivity'    => $this->analytics->todayActivity(),
            'profileCompletion'=> $this->analytics->profileCompletion(),
        ]);
    }

    public function users(): View
    {
        return view('admin.super_admin.analytics.users', [
            'kpis'             => $this->analytics->overviewKpis(),
            'userGrowth'       => $this->analytics->userGrowthChart(12),
            'usersByRegion'    => $this->analytics->usersByRegion(15),
            'profileCompletion'=> $this->analytics->profileCompletion(),
        ]);
    }

    public function leads(): View
    {
        $total     = Lead::count();
        $accepted  = Lead::where('status', 'accepted')->count();
        $rejected  = Lead::where('status', 'rejected')->count();
        $expired   = Lead::where('status', 'expired')->count();
        $converted = Lead::where('status', 'converted')->count();

        return view('admin.super_admin.analytics.leads', [
            'chart'         => $this->analytics->leadsChart(12),
            'topGenerators' => $this->analytics->topLeadGenerators(10),
            'total'         => $total,
            'accepted'      => $accepted,
            'rejected'      => $rejected,
            'expired'       => $expired,
            'converted'     => $converted,
            'rate'          => $total > 0 ? round($accepted / $total * 100, 1) : 0,
        ]);
    }

    public function connections(): View
    {
        $total    = Connection::count();
        $accepted = Connection::where('status', 'accepted')->count();
        $rejected = Connection::where('status', 'rejected')->count();
        $pending  = Connection::where('status', 'pending')->count();

        return view('admin.super_admin.analytics.connections', [
            'chart'    => $this->analytics->connectionsChart(12),
            'total'    => $total,
            'accepted' => $accepted,
            'rejected' => $rejected,
            'pending'  => $pending,
            'rate'     => $total > 0 ? round($accepted / $total * 100, 1) : 0,
        ]);
    }

    public function events(): View
    {
        return view('admin.super_admin.analytics.events', [
            'chart'     => $this->analytics->eventsChart(12),
            'topEvents' => $this->analytics->topEvents(10),
            'total'     => Event::count(),
            'upcoming'  => Event::where('starts_at', '>', now())->count(),
            'completed' => Event::where('ends_at', '<', now())->count(),
        ]);
    }

    public function subscriptions(): View
    {
        return view('admin.super_admin.analytics.subscriptions', [
            'growthChart'  => $this->analytics->subscriptionGrowthChart(12),
            'distribution' => $this->analytics->subscriptionDistribution(),
            'total'        => Subscription::where('status', 'active')->count(),
            'plans'        => Plan::withCount('activeSubscriptions')->orderBy('sort_order')->get(),
            'revenue'      => DB::table('subscriptions')
                ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
                ->where('subscriptions.status', 'active')
                ->sum('plans.price'),
        ]);
    }

    public function ambassadors(): View
    {
        return view('admin.super_admin.analytics.ambassadors', [
            'stats'           => $this->analytics->ambassadorStats(),
            'topAmbassadors'  => $this->analytics->topAmbassadors(10),
            'pendingRequests' => ConsulRequest::with(['user.city'])
                ->where('status', 'pending')
                ->latest()->limit(5)->get(),
        ]);
    }

    public function regional(): View
    {
        return view('admin.super_admin.analytics.regional', [
            'regionalStats' => $this->analytics->regionalStats(),
            'total'         => User::where('role', 'user')->count(),
        ]);
    }

    public function system(): View
    {
        return view('admin.super_admin.analytics.system', [
            'health'            => $this->analytics->systemHealth(),
            'notifications'     => $this->analytics->notificationStats(),
            'todayActivity'     => $this->analytics->todayActivity(),
            'profileCompletion' => $this->analytics->profileCompletion(),
        ]);
    }
}
