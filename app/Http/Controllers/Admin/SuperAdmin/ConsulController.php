<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ConsulRequest;
use App\Models\User;
use App\Services\ConsulService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConsulController extends Controller
{
    public function __construct(private ConsulService $service) {}

    // ── Ambassador requests (from Consuls) ───────────────────────────────────

    public function index(Request $request): View
    {
        $this->authorize('validate', ConsulRequest::class);

        $status = $request->get('status', 'pending');

        $requests = ConsulRequest::with(['user.subscription.plan', 'user.city', 'validator'])
            ->where('status', $status)
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $counts = [
            'pending'  => ConsulRequest::where('status', 'pending')->count(),
            'approved' => ConsulRequest::where('status', 'approved')->count(),
            'rejected' => ConsulRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.super_admin.consul.index', compact('requests', 'status', 'counts'));
    }

    public function approve(ConsulRequest $consulRequest): RedirectResponse
    {
        $this->authorize('validate', ConsulRequest::class);

        try {
            $this->service->approve($consulRequest, auth()->user());
            return back()->with('success', "{$consulRequest->user->first_name} {$consulRequest->user->last_name} est maintenant Ambassadeur.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, ConsulRequest $consulRequest): RedirectResponse
    {
        $this->authorize('validate', ConsulRequest::class);
        $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        try {
            $this->service->reject($consulRequest, auth()->user(), $request->reason);
            return back()->with('success', "Demande de {$consulRequest->user->first_name} refusée.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ── Consul management (admin nominates Premium users as Consul) ──────────

    public function consuls(Request $request): View
    {
        $this->authorize('promoteAmbassador', ConsulRequest::class);

        $query = User::with(['subscription.plan', 'city'])
            ->where('role', 'user')
            ->whereHas('subscription', fn($q) => $q->where('status', 'active')
                ->whereHas('plan', fn($p) => $p->where('price', '>', 0)));

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('first_name', 'like', "%{$s}%")
                ->orWhere('last_name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%"));
        }

        if ($request->filled('status')) {
            match ($request->status) {
                'consul'    => $query->where('consul_status', 'approved'),
                'eligible'  => $query->whereNull('consul_status'),
                default     => null,
            };
        }

        $users = $query->orderBy('first_name')->paginate(25)->withQueryString();

        $counts = [
            'total'   => User::where('role', 'user')->whereHas('subscription', fn($q) => $q->where('status', 'active')->whereHas('plan', fn($p) => $p->where('price', '>', 0)))->count(),
            'consul'  => User::where('consul_status', 'approved')->count(),
            'eligible'=> User::where('role', 'user')->whereHas('subscription', fn($q) => $q->where('status', 'active')->whereHas('plan', fn($p) => $p->where('price', '>', 0)))->whereNull('consul_status')->count(),
        ];

        $pendingRequests = ConsulRequest::with(['user.subscription.plan', 'user.city'])
            ->where('status', ConsulRequest::STATUS_PENDING)
            ->latest()
            ->get();

        $requestCounts = [
            'pending'  => ConsulRequest::where('status', 'pending')->count(),
            'approved' => ConsulRequest::where('status', 'approved')->count(),
            'rejected' => ConsulRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.super_admin.consul.consuls', compact('users', 'counts', 'pendingRequests', 'requestCounts'));
    }

    public function nominateConsul(User $user): RedirectResponse
    {
        $this->authorize('promoteAmbassador', ConsulRequest::class);

        try {
            $this->service->nominateConsul($user, auth()->user());
            return back()->with('success', "{$user->first_name} {$user->last_name} est maintenant Consul.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function revokeConsul(User $user): RedirectResponse
    {
        $this->authorize('promoteAmbassador', ConsulRequest::class);

        try {
            $this->service->revokeConsul($user);
            return back()->with('success', "Rôle Consul retiré à {$user->first_name} {$user->last_name}.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    // ── Ambassador management ────────────────────────────────────────────────

    public function nominateAmbassador(User $user): RedirectResponse
    {
        $this->authorize('promoteAmbassador', ConsulRequest::class);

        try {
            $this->service->nominateAmbassador($user, auth()->user());
            return back()->with('success', "{$user->first_name} {$user->last_name} est maintenant Ambassadeur.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function revokeAmbassador(User $user): RedirectResponse
    {
        $this->authorize('promoteAmbassador', ConsulRequest::class);

        try {
            $this->service->revokeAmbassador($user);
            return back()->with('success', "Rôle Ambassadeur retiré à {$user->first_name} {$user->last_name}.");
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /** @deprecated Kept for route compatibility */
    public function ambassadors(Request $request): View
    {
        return $this->consuls($request);
    }

    /** @deprecated Kept for route compatibility */
    public function promoteAmbassador(User $user): RedirectResponse
    {
        return $this->nominateConsul($user);
    }
}
