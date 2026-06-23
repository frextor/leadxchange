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

    // ── Consul requests (admin + ambassador) ─────────────────────────────────

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
            return back()->with('success', "{$consulRequest->user->first_name} {$consulRequest->user->last_name} est maintenant Consul.");
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

    // ── Ambassador management (admin only) ───────────────────────────────────

    public function ambassadors(Request $request): View
    {
        $this->authorize('promoteAmbassador', ConsulRequest::class);

        // All paid-plan users (eligible for ambassador or already ambassador)
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
            $request->status === 'ambassador'
                ? $query->where('ambassador_status', 'approved')
                : $query->where(fn($q) => $q->whereNull('ambassador_status')->orWhere('ambassador_status', '!=', 'approved'));
        }

        $users = $query->orderBy('first_name')->paginate(25)->withQueryString();

        $counts = [
            'total'      => User::where('role', 'user')->whereHas('subscription', fn($q) => $q->where('status', 'active')->whereHas('plan', fn($p) => $p->where('price', '>', 0)))->count(),
            'ambassador' => User::where('ambassador_status', 'approved')->count(),
            'eligible'   => User::where('role', 'user')->whereHas('subscription', fn($q) => $q->where('status', 'active')->whereHas('plan', fn($p) => $p->where('price', '>', 0)))->where(fn($q) => $q->whereNull('ambassador_status')->orWhere('ambassador_status', '!=', 'approved'))->count(),
        ];

        return view('admin.super_admin.consul.ambassadors', compact('users', 'counts'));
    }

    public function promoteAmbassador(User $user): RedirectResponse
    {
        $this->authorize('promoteAmbassador', ConsulRequest::class);

        try {
            $this->service->promoteAmbassador($user, auth()->user());
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
}
