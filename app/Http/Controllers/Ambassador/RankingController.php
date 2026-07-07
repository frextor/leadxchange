<?php

namespace App\Http\Controllers\Ambassador;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AmbassadorService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RankingController extends Controller
{
    public function __construct(private AmbassadorService $service) {}

    public function index(Request $request): View
    {
        $ambassador  = $request->user()->load('city', 'region', 'ambassadorProfile');
        $myScore     = $this->service->computeScore($ambassador);
        $nationalRank = $this->service->nationalRank($ambassador);
        $topAmbassadors = $this->service->topAmbassadors(10);

        // Regional ambassadors (same region)
        $regionalAmbassadors = User::with(['city', 'region', 'ambassadorProfile'])
            ->where('ambassador_status', 'approved')
            ->where(function ($q) use ($ambassador) {
                if ($ambassador->region_id) {
                    $q->where('region_id', $ambassador->region_id);
                } else {
                    $q->where('city_id', $ambassador->city_id);
                }
            })
            ->get()
            ->map(fn ($a) => ['user' => $a, 'score' => $this->service->computeScore($a)])
            ->sortByDesc('score')
            ->values()
            ->toArray();

        $totalAmbassadors = User::where('ambassador_status', 'approved')->count();

        return view('ambassador.ranking.index', compact(
            'ambassador', 'myScore', 'nationalRank',
            'topAmbassadors', 'regionalAmbassadors', 'totalAmbassadors'
        ));
    }
}
