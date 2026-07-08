<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ConsulService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AmbassadorController extends Controller
{
    public function __construct(private ConsulService $consulService) {}

    /** GET /ambassador/consul-requests — Premium users with pending consul status request */
    public function consulRequests(): JsonResponse
    {
        $users = User::with(['profile', 'city'])
            ->where('consul_status', 'pending')
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn(User $u) => $this->formatUser($u));

        return response()->json(['data' => $users]);
    }

    /** POST /ambassador/consul-requests/{user}/approve */
    public function approveConsulRequest(User $user): JsonResponse
    {
        try {
            $this->consulService->nominateConsul($user, auth()->user());
            return response()->json(['message' => "{$user->first_name} {$user->last_name} est maintenant Consul."]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /** POST /ambassador/consul-requests/{user}/reject */
    public function rejectConsulRequest(User $user): JsonResponse
    {
        if ($user->consul_status !== 'pending') {
            return response()->json(['message' => 'Aucune demande consul en attente pour cet utilisateur.'], 422);
        }

        $user->update(['consul_status' => 'rejected']);

        return response()->json(['message' => "La demande de {$user->first_name} {$user->last_name} a été refusée."]);
    }

    /** GET /ambassador/premium-users?search= — Paid-plan users who are not ambassadors */
    public function premiumUsers(Request $request): JsonResponse
    {
        $query = User::with(['profile', 'city'])
            ->where('role', 'user')
            ->where(fn($q) => $q->whereNull('ambassador_status')->orWhere('ambassador_status', '!=', 'approved'))
            ->whereHas('subscription', fn($q) => $q->where('status', 'active')
                ->whereHas('plan', fn($p) => $p->where('price', '>', 0)));

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('first_name', 'like', "%{$s}%")
                ->orWhere('last_name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%"));
        }

        $users = $query->orderBy('first_name')->limit(30)->get()->map(fn(User $u) => $this->formatUser($u));

        return response()->json(['data' => $users]);
    }

    /** POST /ambassador/nominate/{user} — Directly nominate a premium user as consul */
    public function nominateConsul(User $user): JsonResponse
    {
        try {
            $this->consulService->nominateConsul($user, auth()->user());
            return response()->json(['message' => "{$user->first_name} {$user->last_name} est maintenant Consul."]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function formatUser(User $user): array
    {
        return [
            'id'            => $user->id,
            'full_name'     => "{$user->first_name} {$user->last_name}",
            'avatar'        => $user->profile?->avatar_url,
            'job_title'     => $user->profile?->job_title,
            'company'       => $user->company_name,
            'city'          => $user->city?->name,
            'consul_status' => $user->consul_status,
        ];
    }
}
