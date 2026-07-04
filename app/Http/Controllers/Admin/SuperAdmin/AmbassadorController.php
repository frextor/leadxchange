<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ConsulRequest;
use App\Models\User;
use App\Services\ConsulService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AmbassadorController extends Controller
{
    public function __construct(private ConsulService $service) {}

    public function index(Request $request): View
    {
        $this->authorize('promoteAmbassador', ConsulRequest::class);

        $tab = $request->get('tab', 'nominate');

        // ── KPI counts ───────────────────────────────────────────────────────
        $counts = [
            'ambassador' => User::where('ambassador_status', 'approved')->count(),
            'pending'    => ConsulRequest::where('status', 'pending')->count(),
            'eligible'   => User::where('role', 'user')
                ->whereHas('subscription', fn($q) => $q->where('status', 'active')
                    ->whereHas('plan', fn($p) => $p->where('price', '>', 0)))
                ->whereNull('ambassador_status')
                ->count(),
        ];

        // ── Tab: Nommer — premium users ───────────────────────────────────────
        $nominateQuery = User::with(['subscription.plan', 'city'])
            ->where('role', 'user')
            ->whereHas('subscription', fn($q) => $q->where('status', 'active')
                ->whereHas('plan', fn($p) => $p->where('price', '>', 0)));

        if ($request->filled('search')) {
            $s = $request->search;
            $nominateQuery->where(fn($q) => $q->where('first_name', 'like', "%{$s}%")
                ->orWhere('last_name', 'like', "%{$s}%")
                ->orWhere('email', 'like', "%{$s}%"));
        }

        match ($request->get('filter')) {
            'ambassador' => $nominateQuery->where('ambassador_status', 'approved'),
            'eligible'   => $nominateQuery->whereNull('ambassador_status'),
            default      => null,
        };

        $nominatableUsers = $nominateQuery->orderBy('first_name')->paginate(25)->withQueryString();

        // ── Tab: Demandes — ConsulRequests ────────────────────────────────────
        $reqStatus = $request->get('req_status', 'pending');

        $requests = ConsulRequest::with(['user.subscription.plan', 'user.city', 'validator'])
            ->where('status', $reqStatus)
            ->latest()
            ->paginate(20, ['*'], 'req_page')
            ->withQueryString();

        $requestCounts = [
            'pending'  => ConsulRequest::where('status', 'pending')->count(),
            'approved' => ConsulRequest::where('status', 'approved')->count(),
            'rejected' => ConsulRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.super_admin.ambassadors.index', compact(
            'tab', 'nominatableUsers', 'requests', 'requestCounts', 'counts', 'reqStatus'
        ));
    }
}
