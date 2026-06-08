<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EnterpriseInvitationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnterpriseInvitationController extends Controller
{
    public function __construct(private EnterpriseInvitationService $invitations) {}

    public function index(Request $request): JsonResponse
    {
        $subscription = $this->invitations->activeEnterpriseSubscription($request->user());

        if (!$subscription) {
            return response()->json([
                'message' => 'An active enterprise subscription is required.',
            ], 403);
        }

        $items = $request->user()
            ->sentEnterpriseInvitations()
            ->with('acceptedUser:id,first_name,last_name,email')
            ->where('subscription_id', $subscription->id)
            ->latest()
            ->get()
            ->map(fn($invitation) => [
                'id' => $invitation->id,
                'email' => $invitation->email,
                'status' => $invitation->status,
                'expires_at' => $invitation->expires_at,
                'accepted_at' => $invitation->accepted_at,
                'accepted_user' => $invitation->acceptedUser ? [
                    'id' => $invitation->acceptedUser->id,
                    'name' => trim($invitation->acceptedUser->first_name . ' ' . $invitation->acceptedUser->last_name),
                    'email' => $invitation->acceptedUser->email,
                ] : null,
            ]);

        return response()->json([
            'data' => $items,
            'seats' => [
                'used' => $this->invitations->seatsUsed($subscription),
                'limit' => $this->invitations->seatsLimit($subscription),
                'remaining' => $this->invitations->seatsRemaining($subscription),
            ],
        ]);
    }

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
            'message' => 'Invitation sent.',
            'data' => [
                'id' => $invitation->id,
                'email' => $invitation->email,
                'status' => $invitation->status,
                'expires_at' => $invitation->expires_at,
            ],
        ], 201);
    }

    public function show(string $token): JsonResponse
    {
        try {
            $invitation = $this->invitations->findValidByToken($token);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }

        return response()->json([
            'data' => [
                'email' => $invitation->email,
                'owner_name' => trim($invitation->owner->first_name . ' ' . $invitation->owner->last_name),
                'company_name' => $invitation->owner->company?->name,
                'plan_label' => $invitation->subscription->plan?->label,
                'expires_at' => $invitation->expires_at,
            ],
        ]);
    }

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
                'plan_label' => $invitation->subscription->plan?->label,
                'accepted_at' => $invitation->accepted_at,
            ],
        ]);
    }
}
