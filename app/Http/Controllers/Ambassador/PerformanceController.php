<?php

namespace App\Http\Controllers\Ambassador;

use App\Http\Controllers\Controller;
use App\Models\AmbassadorObjective;
use App\Services\AmbassadorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerformanceController extends Controller
{
    public function __construct(private AmbassadorService $service) {}

    public function index(Request $request): View
    {
        $ambassador = $request->user();
        $progress   = $this->service->currentProgress($ambassador);
        $objective  = $this->service->currentObjective($ambassador);
        $chart      = $this->service->monthlyLeadsChart($ambassador, 6);

        // Last 6 months evolution
        $monthlyHistory = [];
        $fr = ['Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
        for ($i = 5; $i >= 0; $i--) {
            $d    = now()->subMonths($i);
            $obj  = AmbassadorObjective::where('ambassador_id', $ambassador->id)
                ->where('month', $d->month)->where('year', $d->year)->first();

            $monthlyHistory[] = [
                'label'   => $fr[$d->month - 1] . ' ' . $d->format('y'),
                'members' => \App\Models\User::where('role', 'user')
                    ->where($ambassador->region_id ? 'region_id' : 'city_id',
                            $ambassador->region_id ?? $ambassador->city_id)
                    ->whereYear('created_at', $d->year)->whereMonth('created_at', $d->month)->count(),
                'leads'   => \App\Models\Lead::where('sender_id', $ambassador->id)
                    ->whereYear('created_at', $d->year)->whereMonth('created_at', $d->month)->count(),
                'events'  => $this->service->ambassadorEventsQuery($ambassador)
                    ->whereYear('created_at', $d->year)->whereMonth('created_at', $d->month)->count(),
                'target_members' => $obj?->target_members ?? 30,
                'target_leads'   => $obj?->target_leads ?? 100,
                'target_events'  => $obj?->target_events ?? 5,
            ];
        }

        return view('ambassador.performance.index', compact(
            'ambassador', 'progress', 'objective', 'chart', 'monthlyHistory'
        ));
    }

    public function updateObjectives(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'target_members' => 'required|integer|min:1|max:9999',
            'target_events'  => 'required|integer|min:1|max:999',
            'target_leads'   => 'required|integer|min:1|max:9999',
        ]);

        AmbassadorObjective::updateOrCreate(
            [
                'ambassador_id' => $request->user()->id,
                'month'         => now()->month,
                'year'          => now()->year,
            ],
            $data
        );

        return back()->with('success', 'Objectifs mis à jour.');
    }
}
