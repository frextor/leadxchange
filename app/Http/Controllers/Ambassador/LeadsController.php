<?php

namespace App\Http\Controllers\Ambassador;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Services\AmbassadorService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeadsController extends Controller
{
    public function __construct(private AmbassadorService $service) {}

    public function index(Request $request): View
    {
        $ambassador = $request->user();

        $kpis = [
            'generated'       => $this->service->leadsGeneratedCount($ambassador),
            'received'        => $this->service->leadsReceivedCount($ambassador),
            'conversion_rate' => $this->service->leadsConversionRate($ambassador),
        ];

        $chart = $this->service->monthlyLeadsChart($ambassador, 6);

        $sentLeads = Lead::with('receiver.profile', 'receiver.company')
            ->where('sender_id', $ambassador->id)
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $receivedLeads = Lead::with('sender.profile', 'sender.company')
            ->where('receiver_id', $ambassador->id)
            ->latest()
            ->limit(10)
            ->get();

        return view('ambassador.leads.index', compact(
            'ambassador', 'kpis', 'chart', 'sentLeads', 'receivedLeads'
        ));
    }
}
