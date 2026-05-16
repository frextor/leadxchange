<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;
use App\Models\LeadRating;
use App\Models\Sector;
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

        $connectionIds = $user->connectionIds();
        $connections   = User::whereIn('id', $connectionIds)
            ->select('id', 'first_name', 'last_name', 'points_balance')
            ->orderBy('first_name')
            ->get();

        $sectors = Sector::orderBy('name')->get(['id', 'name']);

        return view('leads.index', [
            'received'      => $leads['received'],
            'sent'          => $leads['sent'],
            'connections'   => $connections,
            'sectors'       => $sectors,
            'statusConfig'  => Lead::$statusConfig,
            'qualConfig'    => Lead::$qualificationConfig,
            'currentUser'   => $user,
        ]);
    }

    public function show(Request $request, int $id)
    {
        $user = $request->user();
        $lead = Lead::with([
            'sender:id,first_name,last_name,points_balance,badge_level',
            'receiver:id,first_name,last_name,points_balance,badge_level',
            'ratings',
            'sector:id,name',
        ])->findOrFail($id);

        if ($lead->sender_id !== $user->id && $lead->receiver_id !== $user->id) {
            abort(403);
        }

        $isSent = $lead->sender_id === $user->id;

        return view('leads.show', compact('lead', 'user', 'isSent'));
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

    public function report(Request $request, int $id)
    {
        $request->validate([
            'fraud_reason' => ['required', 'in:fausses_coordonnees,besoin_inexistant,doublon'],
        ]);

        try {
            $this->leadService->reportFraud($request->user(), $id, $request->fraud_reason);

            return back()->with('success', 'Lead signalé comme frauduleux. Merci pour votre vigilance.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
