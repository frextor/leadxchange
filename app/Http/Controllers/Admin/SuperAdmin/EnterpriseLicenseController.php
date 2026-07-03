<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\EnterpriseLicense;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnterpriseLicenseController extends Controller
{
    public function index()
    {
        $licenses = EnterpriseLicense::with(['holder', 'plan'])
            ->withCount(['invitations', 'activeInvitations'])
            ->latest()
            ->paginate(20);

        return view('admin.enterprise.index', compact('licenses'));
    }

    public function create()
    {
        $enterprisePlan = Plan::where('is_enterprise', true)->where('is_active', true)->first();
        $users = User::where('role', 'user')->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email']);

        return view('admin.enterprise.create', compact('enterprisePlan', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'holder_user_id' => ['required', 'exists:users,id'],
            'seats_total'    => ['required', 'integer', 'min:2', 'max:500'],
            'expires_at'     => ['nullable', 'date', 'after:today'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        $enterprisePlan = Plan::where('is_enterprise', true)->where('is_active', true)->firstOrFail();

        // Prevent duplicate license for same holder
        if (EnterpriseLicense::where('holder_user_id', $validated['holder_user_id'])->exists()) {
            return back()->withErrors(['holder_user_id' => 'Cet utilisateur a déjà une licence entreprise.']);
        }

        DB::transaction(function () use ($validated, $enterprisePlan) {
            $license = EnterpriseLicense::create([
                'holder_user_id' => $validated['holder_user_id'],
                'plan_id'        => $enterprisePlan->id,
                'seats_total'    => $validated['seats_total'],
                'seats_used'     => 1, // holder counts as 1
                'notes'          => $validated['notes'] ?? null,
                'expires_at'     => $validated['expires_at'] ?? null,
            ]);

            // Grant enterprise plan to holder
            Subscription::where('user_id', $license->holder_user_id)->update(['status' => 'cancelled']);
            Subscription::create([
                'user_id'            => $license->holder_user_id,
                'plan_id'            => $enterprisePlan->id,
                'status'             => 'active',
                'started_at'         => now(),
                'current_period_end' => $license->expires_at,
            ]);
        });

        return redirect()->route('admin.super.enterprise.index')
            ->with('success', 'Licence entreprise créée et attribuée au titulaire.');
    }

    public function edit(EnterpriseLicense $license)
    {
        $license->load(['holder', 'plan', 'invitations.user']);
        $users = User::where('role', 'user')->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email']);

        return view('admin.enterprise.edit', compact('license', 'users'));
    }

    public function update(Request $request, EnterpriseLicense $license)
    {
        $validated = $request->validate([
            'seats_total' => ['required', 'integer', 'min:' . $license->seats_used, 'max:500'],
            'expires_at'  => ['nullable', 'date'],
            'notes'       => ['nullable', 'string', 'max:500'],
        ]);

        $license->update($validated);

        // Sync expiry date to all active enterprise subscriptions in this license
        $memberIds = $license->invitations()->where('status', 'active')->pluck('user_id')->toArray();
        $memberIds[] = $license->holder_user_id;

        Subscription::whereIn('user_id', $memberIds)
            ->where('plan_id', $license->plan_id)
            ->where('status', 'active')
            ->update(['current_period_end' => $validated['expires_at']]);

        return back()->with('success', 'Licence mise à jour.');
    }

    public function destroy(EnterpriseLicense $license)
    {
        DB::transaction(function () use ($license) {
            // Revoke all member subscriptions, downgrade to basic
            $basicPlan = Plan::where('name', 'basic')->first();

            $memberIds = $license->invitations()->pluck('user_id')->filter()->toArray();
            $memberIds[] = $license->holder_user_id;

            Subscription::whereIn('user_id', $memberIds)
                ->where('plan_id', $license->plan_id)
                ->update(['status' => 'cancelled']);

            if ($basicPlan) {
                foreach ($memberIds as $uid) {
                    Subscription::create([
                        'user_id'    => $uid,
                        'plan_id'    => $basicPlan->id,
                        'status'     => 'active',
                        'started_at' => now(),
                    ]);
                }
            }

            $license->delete(); // cascades to invitations
        });

        return redirect()->route('admin.super.enterprise.index')
            ->with('success', 'Licence entreprise supprimée. Tous les membres ont été rétrogradés en Basic.');
    }
}
