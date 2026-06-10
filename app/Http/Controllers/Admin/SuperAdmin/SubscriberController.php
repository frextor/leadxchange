<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriberController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with(['region', 'subscription.plan'])
            ->where('role', 'user');

        if ($request->filled('region_id')) {
            $query->where('region_id', $request->region_id);
        }

        if ($request->filled('plan')) {
            $query->whereHas('subscription', fn($q) =>
                $q->where('status', 'active')
                  ->whereHas('plan', fn($p) => $p->where('name', $request->plan))
            );
        }

        if ($request->filled('status')) {
            $request->status === 'active'
                ? $query->whereHas('subscription', fn($q) => $q->where('status', 'active'))
                : $query->whereDoesntHave('subscription', fn($q) => $q->where('status', 'active'));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) => $q
                ->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name',  'like', "%{$search}%")
                ->orWhere('email',      'like', "%{$search}%")
            );
        }

        $subscribers = $query->latest()->paginate(25)->withQueryString();
        $regions     = City::orderBy('name')->get(['id', 'name']);
        $plans       = Plan::orderBy('sort_order')->get(['id', 'name', 'label']);

        return view('admin.super_admin.subscribers.index', compact('subscribers', 'regions', 'plans'));
    }
}
