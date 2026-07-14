<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;
use App\Models\PointsHistory;
use App\Services\LeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function __construct(private LeadService $leadService) {}

    public function index(Request $request): JsonResponse
    {
        $tab           = $request->input('tab', 'received');
        $status        = $request->input('status');
        $qualification = $request->input('qualification');
        $sectorId      = $request->filled('sector_id') ? (int) $request->sector_id : null;
        $perPage       = min((int) $request->input('per_page', 15), 50);

        $paginator = $this->leadService->getUserLeadsPaginated(
            $request->user(), $tab, $status, $qualification, $sectorId, $perPage
        );

        return response()->json([
            'data' => $paginator->getCollection()->map(fn($l) => $this->format($l))->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'total'        => $paginator->total(),
                'per_page'     => $paginator->perPage(),
            ],
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->leadService->getDashboardStats($request->user()),
        ]);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        try {
            $this->leadService->cancelLead($request->user(), $id);
            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function store(StoreLeadRequest $request): JsonResponse
    {
        $user     = $request->user();
        $maxLeads = $user->planPermission('max_leads_per_month');

        if ($maxLeads !== null) {
            $sent = \App\Models\Lead::where('sender_id', $user->id)
                ->where('created_at', '>=', now()->startOfMonth())
                ->count();

            if ($sent >= $maxLeads) {
                return response()->json([
                    'message' => "Limite de {$maxLeads} lead(s)/mois atteinte. Passez à un plan supérieur.",
                    'upgrade' => true,
                ], 403);
            }
        }

        try {
            $lead = $this->leadService->createLead($user, $request->validated());

            return response()->json([
                'message' => 'Lead envoyé avec succès.',
                'data'    => $this->format($lead),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $lead = Lead::with([
            'sender:id,first_name,last_name',
            'sender.profile:user_id,avatar',
            'receiver:id,first_name,last_name',
            'receiver.profile:user_id,avatar',
            'ratings',
            'sector:id,name',
        ])->findOrFail($id);

        $user = $request->user();
        if ($lead->sender_id !== $user->id && $lead->receiver_id !== $user->id) {
            return response()->json(['message' => 'Non autorisé.'], 403);
        }

        return response()->json(['data' => $this->format($lead)]);
    }

    public function accept(Request $request, int $id): JsonResponse
    {
        try {
            $lead = $this->leadService->acceptLead($request->user(), $id);

            return response()->json(['message' => 'Lead accepté.', 'data' => $this->format($lead)]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        try {
            $lead = $this->leadService->rejectLead($request->user(), $id);

            return response()->json(['message' => 'Lead refusé.', 'data' => $this->format($lead)]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function convert(Request $request, int $id): JsonResponse
    {
        try {
            $lead = $this->leadService->convertLead($request->user(), $id);

            return response()->json(['message' => 'Lead converti.', 'data' => $this->format($lead)]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function rate(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'quality'    => ['required', 'integer', 'min:1', 'max:5'],
            'relevance'  => ['required', 'integer', 'min:1', 'max:5'],
            'reactivity' => ['required', 'integer', 'min:1', 'max:5'],
            'lead_type'  => ['required', 'in:MQL,SQL,SP'],
        ]);

        try {
            $rating = $this->leadService->rateLead(
                $request->user(),
                $id,
                (int) $request->quality,
                (int) $request->relevance,
                (int) $request->reactivity,
                $request->lead_type,
            );

            return response()->json([
                'message' => 'Notation enregistrée.',
                'data'    => [
                    'quality'      => $rating->quality,
                    'relevance'    => $rating->relevance,
                    'reactivity'   => $rating->reactivity,
                    'average_note' => $rating->average_note,
                    'lead_type'    => $request->lead_type,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function report(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'fraud_reason' => ['required', 'in:false_info,no_need,duplicate,fausses_coordonnees,besoin_inexistant,doublon'],
        ]);

        try {
            $lead = $this->leadService->reportFraud($request->user(), $id, $request->fraud_reason);

            return response()->json([
                'message' => 'Lead signalé comme frauduleux.',
                'data'    => $this->format($lead),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function reschedule(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'deadline' => ['required', 'date', 'after:today'],
        ]);

        try {
            $lead = $this->leadService->rescheduleDeadline($request->user(), $id, $request->deadline);

            return response()->json(['message' => 'Date échéance mise à jour.', 'data' => $this->format($lead)]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function transfer(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'receiver_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        try {
            $lead = $this->leadService->transferLead($request->user(), $id, (int) $request->receiver_id);

            return response()->json(['message' => 'Lead transféré avec succès.', 'data' => $this->format($lead)]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function pointsHistory(Request $request): JsonResponse
    {
        $paginator = PointsHistory::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(20, ['id', 'delta', 'reason', 'balance_after', 'created_at']);

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page'    => $paginator->currentPage(),
                'last_page'       => $paginator->lastPage(),
                'total'           => $paginator->total(),
                'current_balance' => $request->user()->points_balance ?? 0,
                'badge_level'     => $request->user()->badge_level ?? 'bronze',
            ],
        ]);
    }

    private function format(Lead $lead): array
    {
        $sc  = Lead::$statusConfig[$lead->status]          ?? [];
        $qc  = Lead::$qualificationConfig[$lead->qualification] ?? [];
        $avg = $lead->average_rating;

        return [
            'id'               => $lead->id,
            'company_name'     => $lead->company_name,
            'contact_name'     => $lead->contact_name,
            'contact_email'    => $lead->contact_email,
            'contact_phone'    => $lead->contact_phone,
            'contact_position' => $lead->contact_position,
            'deadline'         => $lead->deadline?->toDateString(),
            'qualification'       => $lead->qualification,
            'qualification_label' => $qc['label'] ?? $lead->qualification,
            'lead_type'           => $lead->lead_type,
            'sector'           => $lead->sector ? ['id' => $lead->sector->id, 'name' => $lead->sector->name] : null,
            'description'      => $lead->description,
            'status'           => $lead->status,
            'status_label'     => $sc['label'] ?? $lead->status,
            'average_rating'   => $avg,
            'ratings_count'    => $lead->ratings?->count() ?? 0,
            'fraud_reported'   => (bool) $lead->fraud_reported,
            'fraud_reason'     => $lead->fraud_reason,
            'sender'           => $lead->sender ? [
                'id'     => $lead->sender->id,
                'name'   => $lead->sender->first_name . ' ' . $lead->sender->last_name,
                'avatar' => $lead->sender->profile?->avatar_url,
            ] : null,
            'receiver'         => $lead->receiver ? [
                'id'     => $lead->receiver->id,
                'name'   => $lead->receiver->first_name . ' ' . $lead->receiver->last_name,
                'avatar' => $lead->receiver->profile?->avatar_url,
            ] : null,
            'created_at'       => $lead->created_at->toIso8601String(),
        ];
    }
}
