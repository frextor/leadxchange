<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ConsulRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AmbassadorController extends Controller
{

    public function index(Request $request): View
    {
        $this->authorize('promoteAmbassador', ConsulRequest::class);

        $counts = [
            'ambassador' => User::where('ambassador_status', 'approved')->count(),
            'eligible'   => User::where('role', 'user')
                ->whereHas('subscription', fn($q) => $q->where('status', 'active')
                    ->whereHas('plan', fn($p) => $p->where('price', '>', 0)))
                ->where('ambassador_status', 'none')
                ->count(),
        ];

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
            'eligible'   => $nominateQuery->where('ambassador_status', 'none'),
            default      => null,
        };

        $nominatableUsers = $nominateQuery->orderBy('first_name')->paginate(25)->withQueryString();

        return view('admin.super_admin.ambassadors.index', compact('nominatableUsers', 'counts'));
    }
}
