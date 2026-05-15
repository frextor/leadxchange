<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLeadRequest;
use App\Models\Lead;
use App\Services\LeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function __construct(private LeadService $leadService) {}

    public function index(Request $request): JsonResponse
    {
        $leads = $this->leadService->getUserLeads($request->user());

        return response()->json([
            'data' => [
                'received' => $leads['received']->map(fn($l) => $this->format($l)),
                'sent'     => $leads['sent']->map(fn($l) => $this->format($l)),
            ],
            'meta' => [
                'total_received' => $leads['received']->count(),
                'total_sent'     => $leads['sent']->count(),
                'pending'        => $leads['received']->where('status', Lead::STATUS_NEW)->count(),
            ],
        ]);
    }

    public function store(StoreLeadRequest $request): JsonResponse
    {
        try {
            $lead = $this->leadService->createLead($request->user(), $request->validated());

            return response()->json([
                'message' => 'Lead sent successfully.',
                'data'    => $this->format($lead),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $lead = Lead::with(['sender:id,first_name,last_name', 'receiver:id,first_name,last_name', 'ratings'])->findOrFail($id);

        $user = $request->user();
        if ($lead->sender_id !== $user->id && $lead->receiver_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return response()->json(['data' => $this->format($lead)]);
    }

    public function accept(Request $request, int $id): JsonResponse
    {
        try {
            $lead = $this->leadService->acceptLead($request->user(), $id);

            return response()->json(['message' => 'Lead accepted.', 'data' => $this->format($lead)]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function reject(Request $request, int $id): JsonResponse
    {
        try {
            $lead = $this->leadService->rejectLead($request->user(), $id);

            return response()->json(['message' => 'Lead rejected.', 'data' => $this->format($lead)]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function convert(Request $request, int $id): JsonResponse
    {
        try {
            $lead = $this->leadService->convertLead($request->user(), $id);

            return response()->json(['message' => 'Lead converted.', 'data' => $this->format($lead)]);
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
        ]);

        try {
            $rating = $this->leadService->rateLead(
                $request->user(),
                $id,
                (int) $request->quality,
                (int) $request->relevance,
                (int) $request->reactivity,
            );

            return response()->json([
                'message' => 'Rating saved.',
                'data'    => [
                    'quality'      => $rating->quality,
                    'relevance'    => $rating->relevance,
                    'reactivity'   => $rating->reactivity,
                    'average_note' => $rating->average_note,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function format(Lead $lead): array
    {
        $sc   = Lead::$statusConfig[$lead->status]   ?? [];
        $qc   = Lead::$qualificationConfig[$lead->qualification] ?? [];
        $avg  = $lead->average_rating;

        return [
            'id'               => $lead->id,
            'company_name'     => $lead->company_name,
            'contact_name'     => $lead->contact_name,
            'contact_email'    => $lead->contact_email,
            'contact_phone'    => $lead->contact_phone,
            'contact_position' => $lead->contact_position,
            'deadline'         => $lead->deadline?->toDateString(),
            'qualification'    => $lead->qualification,
            'qualification_label' => $qc['label'] ?? $lead->qualification,
            'description'      => $lead->description,
            'status'           => $lead->status,
            'status_label'     => $sc['label'] ?? $lead->status,
            'average_rating'   => $avg,
            'ratings_count'    => $lead->ratings?->count() ?? 0,
            'sender'           => $lead->sender ? [
                'id'   => $lead->sender->id,
                'name' => $lead->sender->first_name . ' ' . $lead->sender->last_name,
            ] : null,
            'receiver'         => $lead->receiver ? [
                'id'   => $lead->receiver->id,
                'name' => $lead->receiver->first_name . ' ' . $lead->receiver->last_name,
            ] : null,
            'created_at'       => $lead->created_at->toIso8601String(),
        ];
    }
}
