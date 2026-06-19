<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = User::with(['region', 'subscription.plan', 'city'])
            ->where('role', '!=', 'super_admin');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn($q2) => $q2
                ->where('first_name', 'like', "%{$q}%")
                ->orWhere('last_name',  'like', "%{$q}%")
                ->orWhere('email',      'like', "%{$q}%")
            );
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }
        if ($request->filled('plan')) {
            $query->whereHas('subscription', fn($q) => $q
                ->where('status', 'active')
                ->whereHas('plan', fn($q2) => $q2->where('name', $request->plan))
            );
        }
        if ($request->filled('verified')) {
            $query->when($request->verified === '1',
                fn($q) => $q->whereNotNull('email_verified_at'),
                fn($q) => $q->whereNull('email_verified_at')
            );
        }

        $users   = $query->orderByDesc('created_at')->paginate(25)->withQueryString();
        $regions = City::active()->orderBy('name')->get(['id', 'name']);
        $plans   = Plan::orderBy('sort_order')->get(['id', 'name', 'label']);

        $counts = [
            'total' => User::where('role', '!=', 'super_admin')->count(),
            'admin' => User::where('role', 'admin')->count(),
            'unverified' => User::where('role', 'user')->whereNull('email_verified_at')->count(),
        ];

        return view('admin.users.index', compact('users', 'regions', 'plans', 'counts'));
    }

    public function show(User $user): View
    {
        $user->load([
            'profile', 'company.sector', 'region', 'city.country',
            'subscription.plan', 'interests',
        ]);
        $plans = Plan::orderBy('sort_order')->get(['id', 'name', 'label', 'price']);

        $stats = [
            'leads_sent'     => $user->sentLeads()->count(),
            'leads_received' => $user->receivedLeads()->count(),
            'connections'    => \App\Models\Connection::where('status', 'accepted')
                ->where(fn($q) => $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id))
                ->count(),
            'groups'  => $user->groups()->count(),
            'events'  => $user->events()->count(),
        ];

        return view('admin.users.show', compact('user', 'stats', 'plans'));
    }

    public function edit(User $user): View
    {
        $regions = City::active()->orderBy('name')->get(['id', 'name']);
        return view('admin.users.edit', compact('user', 'regions'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'role'           => ['required', 'in:user,admin,super_admin'],
            'points_balance' => ['required', 'integer', 'min:0'],
            'badge_level'    => ['required', 'in:bronze,argent,or'],
        ]);

        if ($user->role === 'super_admin' && auth()->id() !== $user->id) {
            return back()->with('error', 'Impossible de modifier un super administrateur.');
        }

        $user->update($request->only('role', 'points_balance', 'badge_level'));

        return back()->with('success', "{$user->first_name} {$user->last_name} mis à jour.");
    }

    public function changePlan(Request $request, User $user): RedirectResponse
    {
        $request->validate(['plan_id' => ['required', 'exists:plans,id']]);

        $plan = Plan::findOrFail($request->plan_id);

        // Cancel existing active subscriptions
        Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->update(['status' => 'canceled']);

        if ($plan->price > 0) {
            Subscription::create([
                'user_id'   => $user->id,
                'plan_id'   => $plan->id,
                'status'    => 'active',
            ]);
        }

        $label = $plan->price > 0 ? $plan->label : 'Basic (gratuit)';
        return back()->with('success', "Plan de {$user->first_name} {$user->last_name} changé en {$label}.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->role === 'super_admin') {
            return back()->with('error', 'Impossible de supprimer un super administrateur.');
        }
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $name = "{$user->first_name} {$user->last_name}";
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "Utilisateur {$name} supprimé.");
    }
}
