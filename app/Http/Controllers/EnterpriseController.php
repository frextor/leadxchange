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
    // ── Team management (holder only) ────────────────────────────────────────

    public function team(Request $request)
    {
        $user    = $request->user();
        $license = $user->enterpriseLicense()->first();

        abort_unless($license, 403, 'Vous n\'avez pas de licence entreprise.');
        abort_if($license->isExpired(), 403, 'Votre licence entreprise a expiré.');

        $invitations = $license->invitations()
            ->with('user')
            ->orderByRaw("FIELD(status,'active','pending','revoked')")
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

        if ($license->seatsAvailable() <= 0) {
            return back()->with('error', 'Toutes les licences de votre pack sont utilisées. Contactez-nous pour en ajouter.');
        }

        // Check if already invited on this license
        $existing = $license->invitations()->where('email', $email)->first();
        if ($existing) {
            if ($existing->status === 'revoked') {
                // Re-activate a revoked seat
                $existing->update(['status' => 'pending', 'token' => EnterpriseInvitation::generateToken(), 'accepted_at' => null]);
                $license->increment('seats_used');
                $this->sendInvitationEmail($existing, $license);
                return back()->with('success', 'Invitation renvoyée à ' . $email . '.');
            }
            return back()->with('error', $email . ' a déjà été invité(e).');
        }

        // Find or auto-create the user account
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
                'email_verified_at' => now(),
            ]);
            $autoCreated = true;
        }

        $invitation = EnterpriseInvitation::create([
            'license_id'  => $license->id,
            'invited_by'  => $user->id,
            'email'       => $email,
            'user_id'     => $targetUser->id,
            'status'      => 'pending',
            'token'       => EnterpriseInvitation::generateToken(),
        ]);

        $license->increment('seats_used');
        $this->sendInvitationEmail($invitation, $license, $autoCreated ? $tempPassword : null);

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

        if ($invitation->status === 'revoked') {
            return back()->with('info', 'Cette licence est déjà révoquée.');
        }

        $invitation->update(['status' => 'revoked']);
        $license->decrement('seats_used');

        if ($invitation->user_id) {
            $this->downgradeToBasic($invitation->user_id);
        }

        return back()->with('success', 'Licence révoquée pour ' . $invitation->email . '.');
    }

    // ── Join flow (public, tokenised) ─────────────────────────────────────────

    public function join(string $token)
    {
        $invitation = EnterpriseInvitation::with('license.holder')
            ->where('token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        $existingUser = $invitation->user;

        return view('enterprise.join', compact('invitation', 'existingUser'));
    }

    public function processJoin(Request $request, string $token)
    {
        $invitation = EnterpriseInvitation::with('license')
            ->where('token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        $request->validate([
            'first_name' => ['required', 'string', 'max:80'],
            'last_name'  => ['required', 'string', 'max:80'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $targetUser = $invitation->user ?? User::where('email', $invitation->email)->first();

        if (! $targetUser) {
            $targetUser = User::create([
                'first_name'        => $request->first_name,
                'last_name'         => $request->last_name,
                'email'             => $invitation->email,
                'password'          => Hash::make($request->password),
                'role'              => 'user',
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
            'status'      => 'active',
            'accepted_at' => now(),
        ]);

        $this->grantEnterprisePlan($targetUser, $invitation->license);
        auth()->login($targetUser);

        return redirect()->route('dashboard')
            ->with('success', 'Bienvenue ! Votre licence Entreprise est maintenant active.');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function grantEnterprisePlan(User $user, EnterpriseLicense $license): void
    {
        Subscription::where('user_id', $user->id)->update(['status' => 'cancelled']);

        Subscription::create([
            'user_id'            => $user->id,
            'plan_id'            => $license->plan_id,
            'status'             => 'active',
            'started_at'         => now(),
            'current_period_end' => $license->expires_at,
        ]);
    }

    private function downgradeToBasic(int $userId): void
    {
        $basicPlan = Plan::where('name', 'basic')->first();
        Subscription::where('user_id', $userId)->update(['status' => 'cancelled']);

        if ($basicPlan) {
            Subscription::create([
                'user_id'    => $userId,
                'plan_id'    => $basicPlan->id,
                'status'     => 'active',
                'started_at' => now(),
            ]);
        }
    }

    private function sendInvitationEmail(EnterpriseInvitation $invitation, EnterpriseLicense $license, ?string $tempPassword = null): void
    {
        try {
            $license->loadMissing('holder');
            $holderName = trim(($license->holder?->first_name ?? '') . ' ' . ($license->holder?->last_name ?? '')) ?: 'LeadXchange';

            \App\Jobs\SendQueuedEmailJob::dispatch(
                to:       $invitation->email,
                subject:  $holderName . ' vous invite à rejoindre son équipe LeadXchange',
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
