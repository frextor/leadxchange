<?php

namespace App\Http\Controllers\Ambassador;

use App\Http\Controllers\Controller;
use App\Models\ConsulRequest;
use App\Models\Notification;
use App\Models\User;
use App\Services\ConsulService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsulManagementController extends Controller
{
    public function __construct(private ConsulService $service) {}

    public function index(Request $request): View
    {
        $ambassador = auth()->user()->load('subscription.plan', 'city', 'region');

        // Use the city/region locked at appointment time, not the current profile location
        $regionId = $ambassador->ambassador_region_id ?? $ambassador->region_id;
        $cityId   = $ambassador->ambassador_city_id   ?? $ambassador->city_id;

        // Build the base scope: users in the same region (or city as fallback)
        $regionScope = function ($q) use ($regionId, $cityId) {
            if ($regionId) {
                $q->where('region_id', $regionId);
            } else {
                $q->where('city_id', $cityId);
            }
        };

        // Premium users with pending consul request in this region
        $pendingRequests = User::with(['subscription.plan', 'city'])
            ->where('role', 'user')
            ->where('consul_status', 'pending')
            ->where(function ($q) use ($regionId, $cityId) {
                if ($regionId) {
                    $q->where('region_id', $regionId);
                } else {
                    $q->where('city_id', $cityId);
                }
            })
            ->get();

        // Premium users in this region without consul status (eligible to nominate)
        $eligibleUsers = User::with(['subscription.plan', 'city'])
            ->where('role', 'user')
            ->whereNull('consul_status')
            ->whereHas('subscription', fn($q) => $q->where('status', 'active')
                ->whereHas('plan', fn($p) => $p->where('price', '>', 0)))
            ->where(function ($q) use ($regionId, $cityId) {
                if ($regionId) {
                    $q->where('region_id', $regionId);
                } else {
                    $q->where('city_id', $cityId);
                }
            })
            ->orderBy('first_name')
            ->get();

        // Already consul in this region (for context)
        $consuls = User::with(['subscription.plan', 'city'])
            ->where('role', 'user')
            ->where('consul_status', 'approved')
            ->where(function ($q) use ($regionId, $cityId) {
                if ($regionId) {
                    $q->where('region_id', $regionId);
                } else {
                    $q->where('city_id', $cityId);
                }
            })
            ->orderBy('first_name')
            ->get();

        $regionName = $ambassador->region?->name ?? $ambassador->city?->name ?? 'votre région';

        $counts = [
            'pending'  => $pendingRequests->count(),
            'eligible' => $eligibleUsers->count(),
            'consuls'  => $consuls->count(),
        ];

        return view('ambassador.consul-management', compact(
            'ambassador', 'pendingRequests', 'eligibleUsers', 'consuls', 'regionName', 'counts'
        ));
    }

    public function approveConsulRequest(User $user): RedirectResponse
    {
        $ambassador = auth()->user();

        if (! $this->isSameRegion($ambassador, $user)) {
            return back()->with('error', 'Cet utilisateur n\'est pas dans votre région.');
        }

        if ($user->consul_status !== 'pending') {
            return back()->with('error', 'Aucune demande Consul en attente pour cet utilisateur.');
        }

        try {
            $this->service->nominateConsul($user, $ambassador);
            return back()->with('success', "{$user->first_name} {$user->last_name} est maintenant Consul.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function rejectConsulRequest(User $user): RedirectResponse
    {
        $ambassador = auth()->user();

        if (! $this->isSameRegion($ambassador, $user)) {
            return back()->with('error', 'Cet utilisateur n\'est pas dans votre région.');
        }

        if ($user->consul_status !== 'pending') {
            return back()->with('error', 'Aucune demande Consul en attente pour cet utilisateur.');
        }

        $user->update(['consul_status' => null]);

        try {
            Notification::storeForUser(
                $user,
                'consul_request_rejected',
                'Demande Consul refusée',
                'Votre demande de rôle Consul n\'a pas été approuvée pour le moment.',
                ['url' => route('profile.me')]
            );
        } catch (\Throwable) {}

        return back()->with('success', "Demande de {$user->first_name} {$user->last_name} refusée.");
    }

    public function nominateConsul(User $user): RedirectResponse
    {
        $ambassador = auth()->user();

        if (! $this->isSameRegion($ambassador, $user)) {
            return back()->with('error', 'Cet utilisateur n\'est pas dans votre région.');
        }

        try {
            $this->service->nominateConsul($user, $ambassador);
            return back()->with('success', "{$user->first_name} {$user->last_name} est maintenant Consul.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    private function isSameRegion(User $ambassador, User $target): bool
    {
        $regionId = $ambassador->ambassador_region_id ?? $ambassador->region_id;
        $cityId   = $ambassador->ambassador_city_id   ?? $ambassador->city_id;

        if ($regionId) {
            return $target->region_id === $regionId;
        }
        return $target->city_id === $cityId;
    }
}
