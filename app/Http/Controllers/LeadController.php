<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function __construct(private LeadService $leadService) {}

    public function index(Request $request)
    {
        $user  = $request->user();
        $leads = $this->leadService->getUserLeads($user);

        // Only connections can receive leads
        $connectionIds = $user->connectionIds();
        $connections   = User::whereIn('id', $connectionIds)
            ->select('id', 'first_name', 'last_name', 'points_balance')
            ->orderBy('first_name')
            ->get();

        return view('leads.index', [
            'received'      => $leads['received'],
            'sent'          => $leads['sent'],
            'connections'   => $connections,
            'statusConfig'  => Lead::$statusConfig,
            'qualConfig'    => Lead::$qualificationConfig,
            'currentUser'   => $user,
        ]);
    }

    public function store(StoreLeadRequest $request)
    {
        try {
            $this->leadService->createLead($request->user(), $request->validated());

            return redirect()->route('leads.index')
                ->with('success', 'Lead envoyé avec succès !');
        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function accept(Request $request, int $id)
    {
        try {
            $this->leadService->acceptLead($request->user(), $id);

            return back()->with('success', 'Lead accepté.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function reject(Request $request, int $id)
    {
        try {
            $this->leadService->rejectLead($request->user(), $id);

            return back()->with('success', 'Lead refusé.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function convert(Request $request, int $id)
    {
        try {
            $this->leadService->convertLead($request->user(), $id);

            return back()->with('success', 'Lead converti !');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function rate(Request $request, int $id)
    {
        $request->validate([
            'quality'    => ['required', 'integer', 'min:1', 'max:5'],
            'relevance'  => ['required', 'integer', 'min:1', 'max:5'],
            'reactivity' => ['required', 'integer', 'min:1', 'max:5'],
        ]);

        try {
            $this->leadService->rateLead(
                $request->user(),
                $id,
                (int) $request->quality,
                (int) $request->relevance,
                (int) $request->reactivity,
            );

            return back()->with('success', 'Merci pour votre notation !');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
