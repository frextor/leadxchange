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

    /** GET /ambassador/consul-requests — Pending consul requests in ambassador's region/city */
    public function consulRequests(): JsonResponse
    {
        $ambassador = auth()->user();

        $users = User::with(['profile', 'city'])
            ->where('consul_status', 'pending')
            ->where($this->regionScope($ambassador))
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn(User $u) => $this->formatUser($u));

        return response()->json(['data' => $users]);
    }

    /** POST /ambassador/consul-requests/{user}/approve */
    public function approveConsulRequest(User $user): JsonResponse
    {
        $ambassador = auth()->user();

        if (! $this->isSameRegion($ambassador, $user)) {
            return response()->json(['message' => 'Cet utilisateur n\'est pas dans votre région.'], 403);
        }

        try {
            $this->consulService->nominateConsul($user, $ambassador);
            return response()->json(['message' => "{$user->first_name} {$user->last_name} est maintenant Consul."]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    /** POST /ambassador/consul-requests/{user}/reject */
    public function rejectConsulRequest(User $user): JsonResponse
    {
        $ambassador = auth()->user();

        if (! $this->isSameRegion($ambassador, $user)) {
            return response()->json(['message' => 'Cet utilisateur n\'est pas dans votre région.'], 403);
        }

        if ($user->consul_status !== 'pending') {
            return response()->json(['message' => 'Aucune demande consul en attente pour cet utilisateur.'], 422);
        }

        $user->update(['consul_status' => 'rejected']);

        return response()->json(['message' => "La demande de {$user->first_name} {$user->last_name} a été refusée."]);
    }

    /** GET /ambassador/premium-users?search= — Paid-plan users in ambassador's region/city who are not consuls/ambassadors */
    public function premiumUsers(Request $request): JsonResponse
    {
        $ambassador = auth()->user();

        $query = User::with(['profile', 'city'])
            ->where('role', 'user')
            ->where(fn($q) => $q->whereNull('ambassador_status')->orWhere('ambassador_status', '!=', 'approved'))
            ->where(fn($q) => $q->whereNull('consul_status')->orWhere('consul_status', '!=', 'approved'))
            ->whereHas('subscription', fn($q) => $q->where('status', 'active')
                ->whereHas('plan', fn($p) => $p->where('price', '>', 0)))
            ->where($this->regionScope($ambassador));

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

    /** POST /ambassador/nominate/{user} — Directly nominate a premium user as consul (must be in same region) */
    public function nominateConsul(User $user): JsonResponse
    {
        $ambassador = auth()->user();

        if (! $this->isSameRegion($ambassador, $user)) {
            return response()->json(['message' => 'Cet utilisateur n\'est pas dans votre région.'], 403);
        }

        try {
            $this->consulService->nominateConsul($user, $ambassador);
            return response()->json(['message' => "{$user->first_name} {$user->last_name} est maintenant Consul."]);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    private function isSameRegion(User $ambassador, User $target): bool
    {
        if ($ambassador->region_id) {
            return $target->region_id === $ambassador->region_id;
        }
        return $target->city_id === $ambassador->city_id;
    }

    private function regionScope(User $ambassador): \Closure
    {
        return function ($q) use ($ambassador) {
            if ($ambassador->region_id) {
                $q->where('region_id', $ambassador->region_id);
            } else {
                $q->where('city_id', $ambassador->city_id);
            }
        };
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
