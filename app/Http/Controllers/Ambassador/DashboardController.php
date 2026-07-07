<?php

namespace App\Http\Controllers\Ambassador;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\AmbassadorService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private AmbassadorService $service) {}

    public function index(Request $request): View
    {
        $ambassador = $request->user()->load('profile', 'city', 'region', 'ambassadorProfile');

        $kpis        = $this->service->dashboardKpis($ambassador);
        $progress    = $this->service->currentProgress($ambassador);
        $leadsChart  = $this->service->monthlyLeadsChart($ambassador, 6);
        $regionName  = $ambassador->region?->name ?? $ambassador->city?->name ?? 'votre région';

        $upcomingEvents = $this->service->ambassadorEventsQuery($ambassador)
            ->where('starts_at', '>', now())
            ->orderBy('starts_at')
            ->with('city')
            ->limit(5)
            ->get();

        $recentMembers = $this->service->regionMembersQuery($ambassador)
            ->with(['profile', 'subscription.plan'])
            ->latest()
            ->limit(5)
            ->get();

        return view('ambassador.dashboard', compact(
            'ambassador', 'kpis', 'progress', 'leadsChart',
            'regionName', 'upcomingEvents', 'recentMembers'
        ));
    }
}
