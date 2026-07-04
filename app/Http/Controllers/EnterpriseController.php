<?php

namespace App\Http\Controllers;

use App\Models\EnterpriseInvitation;
use App\Models\EnterpriseLicense;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EnterpriseController extends Controller
{
    // ── Team management (holder only) ─────────────────────────────────────────

    public function team(Request $request)
    {
        $user    = $request->user();
        $license = $user->enterpriseLicense()->with('invitations.user')->first();

        abort_unless($license, 403, 'Vous n\'avez pas de licence entreprise.');
        abort_if($license->isExpired(), 403, 'Votre licence entreprise a expiré.');

        $invitations = $license->invitations()
            ->with('user')
            ->orderByRaw("FIELD(status,'active','pending','available','revoked')")
            ->latest()
            ->get();

        return view('enterprise.team', compact('license', 'invitations'));
    }

    public function invite(Request $request)
    {
        $user    = $request->user();
        $license = $user->enterpriseLicense()->first();

        abort_unless($license, 403);
        abort_if($license->isExpired(), 403, 'Votre licence entreprise a expiré.');

        $request->validate(['email' => ['required', 'email', 'max:255']]);
        $email = strtolower(trim($request->email));

        if ($email === strtolower($user->email)) {
            return back()->with('error', 'Vous ne pouvez pas vous inviter vous-même.');
        }

        // Already has active/pending invitation on this license
        $existingActive = $license->invitations()
            ->whereNotNull('email')
            ->where('email', $email)
            ->whereIn('status', ['pending', 'active'])
            ->first();

        if ($existingActive) {
            return back()->with('error', $email . ' possède déjà une licence active ou en attente.');
        }

        // Claim an available slot
        $slot = $license->invitations()->where('status', EnterpriseInvitation::STATUS_AVAILABLE)->first();

        if (! $slot) {
            return back()->with('error', 'Aucune licence disponible. Augmentez votre quota ou révoquez un membre inactif.');
        }

        $targetUser  = User::where('email', $email)->first();
        $autoCreated = false;
        $tempPassword = null;

        if (! $targetUser) {
            $tempPassword = Str::random(12);
            $targetUser   = User::create([
                'first_name'        => explode('@', $email)[0],
                'last_name'         => '',
                'email'             => $email,
                'password'          => Hash::make($tempPassword),
                'role'              => 'user',
                'points_balance'    => 0,
                'badge_level'       => 'neutre',
                'email_verified_at' => now(),
            ]);
            $autoCreated = true;
        }

        $slot->update([
            'email'      => $email,
            'user_id'    => $targetUser->id,
            'status'     => EnterpriseInvitation::STATUS_PENDING,
            'invited_by' => $user->id,
            'token'      => EnterpriseInvitation::generateToken(),
        ]);

        $license->increment('seats_used');
        $this->sendInvitationEmail($slot, $license, $autoCreated ? $tempPassword : null);

        return back()->with('success', $autoCreated
            ? "Compte créé et invitation envoyée à {$email}."
            : "Invitation envoyée à {$email}."
        );
    }

    public function revoke(Request $request, int $invId)
    {
        $user    = $request->user();
        $license = $user->enterpriseLicense()->first();
        abort_unless($license, 403);

        $invitation = $license->invitations()->findOrFail($invId);

        if ($invitation->status === EnterpriseInvitation::STATUS_AVAILABLE) {
            return back()->with('info', 'Cette licence est déjà disponible.');
        }

        // Downgrade the member if they had been granted a plan
        if ($invitation->user_id && in_array($invitation->status, ['active', 'pending'])) {
            $this->downgradeToBasic($invitation->user_id);
            $license->decrement('seats_used');
        }

        // Reset slot back to available for re-use
        $invitation->update([
            'status'      => EnterpriseInvitation::STATUS_AVAILABLE,
            'email'       => null,
            'user_id'     => null,
            'invited_by'  => null,
            'accepted_at' => null,
            'token'       => EnterpriseInvitation::generateToken(),
        ]);

        return back()->with('success', 'Licence libérée. Le membre a été rétrogradé en Basic. La licence est à nouveau disponible.');
    }

    // ── Join flow (public, tokenised) ─────────────────────────────────────────

    public function join(string $token)
    {
        $invitation = EnterpriseInvitation::with('license.holder')
            ->where('token', $token)
            ->whereIn('status', [EnterpriseInvitation::STATUS_PENDING, EnterpriseInvitation::STATUS_AVAILABLE])
            ->firstOrFail();

        $existingUser = $invitation->user ?? ($invitation->email ? User::where('email', $invitation->email)->first() : null);

        return view('enterprise.join', compact('invitation', 'existingUser'));
    }

    public function processJoin(Request $request, string $token)
    {
        $invitation = EnterpriseInvitation::with('license')
            ->where('token', $token)
            ->whereIn('status', [EnterpriseInvitation::STATUS_PENDING, EnterpriseInvitation::STATUS_AVAILABLE])
            ->firstOrFail();

        $wasAvailable = $invitation->status === EnterpriseInvitation::STATUS_AVAILABLE;

        // Determine validation rules based on whether email is pre-set
        $rules = [
            'first_name' => ['required', 'string', 'max:80'],
            'last_name'  => ['required', 'string', 'max:80'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
        ];
        if ($wasAvailable) {
            $rules['email'] = ['required', 'email', 'max:255'];
        }

        $request->validate($rules);

        if ($wasAvailable) {
            $email = strtolower(trim($request->email));
            $targetUser = User::where('email', $email)->first();

            if (! $targetUser) {
                $targetUser = User::create([
                    'first_name'        => $request->first_name,
                    'last_name'         => $request->last_name,
                    'email'             => $email,
                    'password'          => Hash::make($request->password),
                    'role'              => 'user',
                    'points_balance'    => 0,
                    'badge_level'       => 'neutre',
                    'email_verified_at' => now(),
                ]);
            } else {
                $targetUser->update([
                    'first_name' => $request->first_name,
                    'last_name'  => $request->last_name,
                    'password'   => Hash::make($request->password),
                ]);
            }

            $invitation->update([
                'email'       => $email,
                'user_id'     => $targetUser->id,
                'status'      => EnterpriseInvitation::STATUS_ACTIVE,
                'accepted_at' => now(),
            ]);

            $invitation->license->increment('seats_used');
        } else {
            // Email was pre-set (invited via email)
            $targetUser = $invitation->user ?? User::where('email', $invitation->email)->first();

            if (! $targetUser) {
                $targetUser = User::create([
                    'first_name'        => $request->first_name,
                    'last_name'         => $request->last_name,
                    'email'             => $invitation->email,
                    'password'          => Hash::make($request->password),
                    'role'              => 'user',
                    'points_balance'    => 0,
                    'badge_level'       => 'neutre',
                    'email_verified_at' => now(),
                ]);
            } else {
                $targetUser->update([
                    'first_name' => $request->first_name,
                    'last_name'  => $request->last_name,
                    'password'   => Hash::make($request->password),
                ]);
            }

            $invitation->update([
                'user_id'     => $targetUser->id,
                'status'      => EnterpriseInvitation::STATUS_ACTIVE,
                'accepted_at' => now(),
            ]);
        }

        $this->grantEnterprisePlan($targetUser, $invitation->license);
        auth()->login($targetUser);

        return redirect()->route('dashboard')
            ->with('success', 'Bienvenue ! Votre licence Premium « ' . $invitation->license->company_name . ' » est maintenant active.');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function grantEnterprisePlan(User $user, EnterpriseLicense $license): void
    {
        Subscription::where('user_id', $user->id)->update(['status' => 'canceled']);

        Subscription::create([
            'user_id'            => $user->id,
            'plan_id'            => $license->plan_id,
            'status'             => 'active',
            'current_period_end' => $license->expires_at,
        ]);
    }

    private function downgradeToBasic(int $userId): void
    {
        $basicPlan = Plan::where('name', 'basic')->first();
        Subscription::where('user_id', $userId)->update(['status' => 'canceled']);

        if ($basicPlan) {
            Subscription::create([
                'user_id' => $userId,
                'plan_id' => $basicPlan->id,
                'status'  => 'active',
            ]);
        }
    }

    private function sendInvitationEmail(EnterpriseInvitation $invitation, EnterpriseLicense $license, ?string $tempPassword = null): void
    {
        try {
            $license->loadMissing('holder');
            $holderName = trim(($license->holder?->first_name ?? '') . ' ' . ($license->holder?->last_name ?? '')) ?: 'LeadXchange';
            $company    = $license->company_name ?: $holderName;

            \App\Jobs\SendQueuedEmailJob::dispatch(
                to:       $invitation->email,
                subject:  $company . ' vous invite à rejoindre son équipe LeadXchange',
                type:     'enterprise_invitation',
                mailable: new \App\Mail\EnterpriseInvitationMail($invitation, $holderName, $tempPassword),
                toName:   $invitation->email,
                metadata: ['invitation_id' => $invitation->id],
            );
        } catch (\Exception $e) {
            Log::warning('Enterprise invitation email failed', [
                'invitation_id' => $invitation->id,
                'error'         => $e->getMessage(),
            ]);
        }
    }
}
