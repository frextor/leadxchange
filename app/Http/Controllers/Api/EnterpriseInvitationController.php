<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EnterpriseInvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnterpriseInvitationController extends Controller
{
    public function __construct(private EnterpriseInvitationService $invitations) {}

    /** List all invitation slots for the authenticated holder. */
    public function index(Request $request): JsonResponse
    {
        $license = $this->invitations->licenseForHolder($request->user());

        if (! $license) {
            return response()->json(['message' => 'Vous n\'avez pas de licence entreprise active.'], 403);
        }

        $items = $license->invitations()
            ->with('user:id,first_name,last_name,email')
            ->orderByRaw("FIELD(status,'active','pending','available','revoked')")
            ->latest()
            ->get()
            ->map(fn($inv) => [
                'id'          => $inv->id,
                'email'       => $inv->email,
                'status'      => $inv->status,
                'accepted_at' => $inv->accepted_at,
                'user'        => $inv->user ? [
                    'id'    => $inv->user->id,
                    'name'  => trim($inv->user->first_name . ' ' . $inv->user->last_name),
                    'email' => $inv->user->email,
                ] : null,
            ]);

        return response()->json([
            'data'  => $items,
            'seats' => $this->invitations->seatsInfo($license),
            'license' => [
                'company_name' => $license->company_name,
                'expires_at'   => $license->expires_at,
            ],
        ]);
    }

    /** Send an invitation to an email address (claims an available slot). */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ]);

        try {
            $invitation = $this->invitations->invite($request->user(), $validated['email']);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Invitation envoyée.',
            'data' => [
                'id'     => $invitation->id,
                'email'  => $invitation->email,
                'status' => $invitation->status,
            ],
        ], 201);
    }

    /** Get invitation details by token (public — used before account creation). */
    public function show(string $token): JsonResponse
    {
        try {
            $invitation = $this->invitations->findValidByToken($token);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        return response()->json([
            'data' => [
                'email'        => $invitation->email,
                'holder_name'  => trim(($invitation->license->holder?->first_name ?? '') . ' ' . ($invitation->license->holder?->last_name ?? '')),
                'company_name' => $invitation->license->company_name,
                'plan_label'   => $invitation->license->plan?->label,
                'expires_at'   => $invitation->license->expires_at,
            ],
        ]);
    }

    /** Accept an enterprise invitation (authenticated user). */
    public function accept(string $token, Request $request): JsonResponse
    {
        try {
            $invitation = $this->invitations->acceptForUser($token, $request->user());
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Enterprise invitation accepted.',
            'data' => [
                'plan_label'   => $invitation->license->plan?->label,
                'company_name' => $invitation->license->company_name,
                'accepted_at'  => $invitation->accepted_at,
            ],
        ]);
    }
}
