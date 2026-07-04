<?php

namespace App\Http\Controllers\Admin\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\EnterpriseInvitation;
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
        $users = User::where('role', 'user')->orderBy('first_name')->get(['id', 'first_name', 'last_name', 'email']);
        return view('admin.enterprise.create', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name'   => ['required', 'string', 'max:100'],
            'holder_user_id' => ['required', 'exists:users,id'],
            'seats_total'    => ['required', 'integer', 'min:2', 'max:500'],
            'expires_at'     => ['nullable', 'date', 'after:today'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        if (EnterpriseLicense::where('holder_user_id', $validated['holder_user_id'])->exists()) {
            return back()->withErrors(['holder_user_id' => 'Cet utilisateur a déjà une licence entreprise.']);
        }

        $premiumPlan = Plan::where('name', 'premium')->where('is_active', true)->firstOrFail();

        DB::transaction(function () use ($validated, $premiumPlan) {
            $license = EnterpriseLicense::create([
                'company_name'   => $validated['company_name'],
                'holder_user_id' => $validated['holder_user_id'],
                'plan_id'        => $premiumPlan->id,
                'seats_total'    => $validated['seats_total'],
                'seats_used'     => 1,
                'notes'          => $validated['notes'] ?? null,
                'expires_at'     => $validated['expires_at'] ?? null,
            ]);

            // Grant Premium plan to holder
            Subscription::where('user_id', $license->holder_user_id)->update(['status' => 'canceled']);
            Subscription::create([
                'user_id'            => $license->holder_user_id,
                'plan_id'            => $premiumPlan->id,
                'status'             => 'active',
                'current_period_end' => $license->expires_at,
            ]);

            // Auto-generate all available seat tokens (holder already counted as 1)
            $availableSeats = $validated['seats_total'] - 1;
            for ($i = 0; $i < $availableSeats; $i++) {
                EnterpriseInvitation::create([
                    'license_id' => $license->id,
                    'invited_by' => null,
                    'email'      => null,
                    'user_id'    => null,
                    'status'     => EnterpriseInvitation::STATUS_AVAILABLE,
                    'token'      => EnterpriseInvitation::generateToken(),
                ]);
            }
        });

        return redirect()->route('admin.super.enterprise.index')
            ->with('success', 'Pack « ' . $validated['company_name'] . ' » créé — ' . ($validated['seats_total'] - 1) . ' licences générées automatiquement.');
    }

    public function edit(EnterpriseLicense $license)
    {
        $license->load(['holder', 'plan', 'invitations.user']);
        return view('admin.enterprise.edit', compact('license'));
    }

    public function update(Request $request, EnterpriseLicense $license)
    {
        $usedSeats = 1 + $license->invitations()->whereIn('status', ['pending', 'active'])->count();

        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:100'],
            'seats_total'  => ['required', 'integer', 'min:' . $usedSeats, 'max:500'],
            'expires_at'   => ['nullable', 'date'],
            'notes'        => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($validated, $license, $usedSeats) {
            $oldTotal = $license->seats_total;
            $newTotal = (int) $validated['seats_total'];

            $license->update([
                'company_name' => $validated['company_name'],
                'seats_total'  => $newTotal,
                'expires_at'   => $validated['expires_at'] ?? null,
                'notes'        => $validated['notes'] ?? null,
            ]);

            if ($newTotal > $oldTotal) {
                // Add more available slots
                $extra = $newTotal - $oldTotal;
                for ($i = 0; $i < $extra; $i++) {
                    EnterpriseInvitation::create([
                        'license_id' => $license->id,
                        'invited_by' => null,
                        'email'      => null,
                        'status'     => EnterpriseInvitation::STATUS_AVAILABLE,
                        'token'      => EnterpriseInvitation::generateToken(),
                    ]);
                }
            } elseif ($newTotal < $oldTotal) {
                // Remove excess available slots (can only remove unassigned ones)
                $toRemove = $oldTotal - $newTotal;
                $license->invitations()
                    ->where('status', EnterpriseInvitation::STATUS_AVAILABLE)
                    ->latest()
                    ->limit($toRemove)
                    ->each(fn($inv) => $inv->delete());
            }

            // Sync expiry on all active member subscriptions
            $memberIds = $license->invitations()->where('status', 'active')->pluck('user_id')->filter()->toArray();
            $memberIds[] = $license->holder_user_id;

            Subscription::whereIn('user_id', $memberIds)
                ->where('plan_id', $license->plan_id)
                ->where('status', 'active')
                ->update(['current_period_end' => $validated['expires_at'] ?? null]);
        });

        return back()->with('success', 'Licence mise à jour.');
    }

    public function destroy(EnterpriseLicense $license)
    {
        DB::transaction(function () use ($license) {
            $basicPlan = Plan::where('name', 'basic')->first();

            $memberIds = $license->invitations()
                ->whereNotNull('user_id')
                ->whereIn('status', ['active', 'pending'])
                ->pluck('user_id')
                ->filter()
                ->toArray();
            $memberIds[] = $license->holder_user_id;

            Subscription::whereIn('user_id', $memberIds)
                ->where('plan_id', $license->plan_id)
                ->where('status', 'active')
                ->update(['status' => 'canceled']);

            if ($basicPlan) {
                foreach (array_unique($memberIds) as $uid) {
                    Subscription::create([
                        'user_id' => $uid,
                        'plan_id' => $basicPlan->id,
                        'status'  => 'active',
                    ]);
                }
            }

            $license->delete();
        });

        return redirect()->route('admin.super.enterprise.index')
            ->with('success', 'Pack entreprise supprimé. Tous les membres ont été rétrogradés en Basic.');
    }
}
