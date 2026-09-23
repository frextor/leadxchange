<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\EnforcePlanLimits;
use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;
use App\Models\LeadRating;
use App\Models\Sector;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    use EnforcePlanLimits;

    public function __construct(private LeadService $leadService) {}

    /**
     * Liste des leads (maquette lx2 : liste + détail côte à côte).
     * Sans lead sélectionné, le détail affiche le premier lead de l'onglet (vue « no-sel » sur mobile).
     */
    public function index(Request $request)
    {
        $box = $request->query('box') === 'sent' ? 'sent' : 'received';

        return $this->renderList($request, $box, null);
    }

    /** Détail d'un lead : même écran, avec ce lead sélectionné (vue « sel » sur mobile). */
    public function show(Request $request, int $id)
    {
        $user = $request->user();
        $lead = Lead::findOrFail($id);

        if ($lead->sender_id !== $user->id && $lead->receiver_id !== $user->id) {
            abort(403);
        }

        return $this->renderList($request, $lead->sender_id === $user->id ? 'sent' : 'received', $lead->id);
    }

    /** Formulaire « Envoyer un lead » (écran dédié de la maquette). */
    public function create(Request $request)
    {
        if ($redirect = $this->requirePermission('can_send_leads')) {
            return $redirect;
        }

        $user        = $request->user();
        $connections = User::with('profile')
            ->whereIn('id', $user->connectionIds())
            ->select('id', 'first_name', 'last_name', 'points_balance')
            ->orderBy('first_name')
            ->get();

        return view('leads.create', [
            'connections'    => $connections,
            'ratings'        => $this->leadService->averageRatingsForSenders($connections->pluck('id')),
            'sectors'        => Sector::orderBy('name')->get(['id', 'name']),
            'preselectedId'  => (int) $request->query('to', 0) ?: null,
            'pointsOnAccept' => \App\Services\PointsService::SEND_CREDIT,
        ]);
    }

    private function renderList(Request $request, string $box, ?int $selectedId)
    {
        $user  = $request->user();
        $leads = $this->leadService->getUserLeads($user);
        $list  = $box === 'sent' ? $leads['sent'] : $leads['received'];

        $selected = $selectedId ? $list->firstWhere('id', $selectedId) : $list->first();

        if ($selected) {
            $selected->loadMissing(['sender.profile', 'receiver.profile', 'sender.company', 'receiver.company']);
        }

        $other = $selected ? ($box === 'sent' ? $selected->receiver : $selected->sender) : null;

        return view('leads.index', [
            'box'          => $box,
            'leads'        => $list,
            'counts'       => ['received' => $leads['received']->count(), 'sent' => $leads['sent']->count()],
            'selected'     => $selected,
            'isSelected'   => $selectedId !== null,
            'otherRating'  => $other ? $this->leadService->averageRatingsForSenders([$other->id])->get($other->id) : null,
            'currentUser'  => $user,
        ]);
    }

    public function store(StoreLeadRequest $request)
    {
        $user = $request->user();

        if ($redirect = $this->requirePermission('can_send_leads')) {
            return $redirect;
        }

        $maxLeads = $user->planPermission('max_leads_per_month'); // null = illimité

        if ($maxLeads !== null) {
            $sentThisMonth = Lead::where('sender_id', $user->id)
                ->where('created_at', '>=', now()->startOfMonth())
                ->count();

            if ($sentThisMonth >= $maxLeads) {
                return $this->upgradeDenied(
                    "Vous avez atteint votre limite de {$maxLeads} lead(s) par mois. Passez à un plan supérieur pour envoyer davantage."
                );
            }
        }

        try {
            $this->leadService->createLead($user, $request->validated());

            return redirect()->route('leads.index', ['box' => 'sent'])
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
            'lead_type'  => ['nullable', 'in:MQL,SQL,SP'],
        ]);

        try {
            $this->leadService->rateLead(
                $request->user(),
                $id,
                (int) $request->quality,
                (int) $request->relevance,
                (int) $request->reactivity,
                $request->input('lead_type'),
            );

            return back()->with('success', 'Merci pour votre notation !');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function report(Request $request, int $id)
    {
        $request->validate([
            'fraud_reason' => ['required', 'in:faux_profil,lead_frauduleux,spam,comportement_inapproprie,autre'],
        ]);

        try {
            $this->leadService->reportFraud($request->user(), $id, $request->fraud_reason);

            return back()->with('success', 'Lead signalé comme frauduleux. Merci pour votre vigilance.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
