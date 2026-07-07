<?php

namespace App\Http\Controllers\Ambassador;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Lead;
use App\Services\AmbassadorService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportsController extends Controller
{
    public function __construct(private AmbassadorService $service) {}

    public function index(Request $request): View
    {
        $ambassador = $request->user();

        $stats = [
            'members' => $this->service->regionMembersCount($ambassador),
            'events'  => $this->service->totalEventsCount($ambassador),
            'leads'   => $this->service->leadsGeneratedCount($ambassador),
        ];

        return view('ambassador.reports.index', compact('ambassador', 'stats'));
    }

    public function export(Request $request, string $type): Response
    {
        $ambassador = $request->user();

        return match ($type) {
            'members' => $this->exportMembers($ambassador),
            'events'  => $this->exportEvents($ambassador),
            'leads'   => $this->exportLeads($ambassador),
            default   => abort(404),
        };
    }

    private function exportMembers(mixed $ambassador): Response
    {
        $members = $this->service->regionMembersQuery($ambassador)
            ->with(['city', 'subscription.plan', 'company'])
            ->get();

        $csv = "Prénom,Nom,Email,Ville,Entreprise,Plan,Membre depuis\n";
        foreach ($members as $m) {
            $csv .= implode(',', [
                "\"{$m->first_name}\"",
                "\"{$m->last_name}\"",
                $m->email,
                "\"{$m->city?->name}\"",
                "\"{$m->company?->name}\"",
                "\"{$m->subscription?->plan?->label}\"",
                $m->created_at->format('d/m/Y'),
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="membres-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    private function exportEvents(mixed $ambassador): Response
    {
        $events = $this->service->ambassadorEventsQuery($ambassador)->with('city')->get();

        $csv = "Titre,Type,Date début,Date fin,Lieu,Participants,Statut\n";
        foreach ($events as $e) {
            $csv .= implode(',', [
                "\"{$e->title}\"",
                $e->type,
                $e->starts_at?->format('d/m/Y H:i'),
                $e->ends_at?->format('d/m/Y H:i'),
                "\"{$e->location}\"",
                $e->attendees_count,
                $e->is_public ? 'publié' : 'annulé',
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="evenements-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    private function exportLeads(mixed $ambassador): Response
    {
        $leads = Lead::with('receiver')->where('sender_id', $ambassador->id)->latest()->get();

        $csv = "Entreprise,Contact,Email,Statut,Qualification,Date\n";
        foreach ($leads as $l) {
            $csv .= implode(',', [
                "\"{$l->company_name}\"",
                "\"{$l->contact_name}\"",
                $l->contact_email,
                $l->status,
                $l->qualification,
                $l->created_at->format('d/m/Y'),
            ]) . "\n";
        }

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="leads-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
