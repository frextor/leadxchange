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

        // Scope: match ambassador's locked region (by region_id, or city_id as fallback)
        $inAmbassadorTerritory = function ($q) use ($regionId, $cityId) {
            if ($regionId) {
                $q->where('region_id', $regionId);
            } elseif ($cityId) {
                $q->where('city_id', $cityId);
            } else {
                // No territory defined — show nothing to prevent leaking other regions
                $q->whereRaw('1 = 0');
            }
        };

        // Premium users with pending consul request in this region (by current location)
        $pendingRequests = User::with(['subscription.plan', 'city'])
            ->where('role', 'user')
            ->where('consul_status', 'pending')
            ->where($inAmbassadorTerritory)
            ->get();

        // Premium users in this region without consul status (eligible to nominate)
        $eligibleUsers = User::with(['subscription.plan', 'city'])
            ->where('role', 'user')
            ->whereNull('consul_status')
            ->whereHas('subscription', fn($q) => $q->where('status', 'active')
                ->whereHas('plan', fn($p) => $p->where('price', '>', 0)))
            ->where($inAmbassadorTerritory)
            ->orderBy('first_name')
            ->get();

        // Consuls in this region — use consul_region_id/consul_city_id (locked at nomination)
        // so a consul who moved city still belongs to the right ambassador
        $consuls = User::with(['subscription.plan', 'city'])
            ->where('role', 'user')
            ->where('consul_status', 'approved')
            ->where(function ($q) use ($regionId, $cityId) {
                if ($regionId) {
                    $q->where('consul_region_id', $regionId);
                } elseif ($cityId) {
                    $q->where('consul_city_id', $cityId);
                } else {
                    $q->whereRaw('1 = 0');
                }
            })
            ->orderBy('first_name')
            ->get();

        // Region name from locked ambassador territory (not current profile location)
        $regionName = $this->resolveAmbassadorRegionName($ambassador);

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

    /**
     * Check that a target user belongs to the ambassador's locked territory.
     * For pending/eligible users: compare current location.
     * For already-consul users: compare their locked consul_region_id / consul_city_id.
     */
    private function isSameRegion(User $ambassador, User $target): bool
    {
        $regionId = $ambassador->ambassador_region_id ?? $ambassador->region_id;
        $cityId   = $ambassador->ambassador_city_id   ?? $ambassador->city_id;

        if (! $regionId && ! $cityId) {
            return false; // ambassador has no territory defined — block all actions
        }

        // If the target is already a consul, compare their locked nomination location
        if ($target->consul_status === 'approved') {
            if ($regionId) {
                return (int) $target->consul_region_id === (int) $regionId;
            }
            return (int) $target->consul_city_id === (int) $cityId;
        }

        // Pending / eligible: compare current profile location
        if ($regionId) {
            return (int) $target->region_id === (int) $regionId;
        }
        return (int) $target->city_id === (int) $cityId;
    }

    /** Resolve the region/city name from the ambassador's locked territory. */
    private function resolveAmbassadorRegionName(User $ambassador): string
    {
        if ($ambassador->ambassador_region_id) {
            $region = \App\Models\Region::find($ambassador->ambassador_region_id);
            if ($region) return $region->name;
        }

        if ($ambassador->ambassador_city_id) {
            $city = \App\Models\City::find($ambassador->ambassador_city_id);
            if ($city) return $city->name;
        }

        // Fallback: current profile location
        return $ambassador->region?->name ?? $ambassador->city?->name ?? 'votre région';
    }
}
