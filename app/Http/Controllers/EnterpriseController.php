<?php

namespace App\Http\Controllers;

use App\Mail\SystemNotificationMail;
use App\Models\EnterpriseInvitation;
use App\Models\EnterpriseLicense;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

        $targetUser = User::where('email', $email)->first();

        $slot->update([
            'email'      => $email,
            'user_id'    => $targetUser?->id,
            'status'     => EnterpriseInvitation::STATUS_PENDING,
            'invited_by' => $user->id,
            'token'      => EnterpriseInvitation::generateToken(),
        ]);

        $license->increment('seats_used');
        $this->sendInvitationEmail($slot, $license);

        return back()->with('success', "Invitation envoyée à {$email}.");
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

    // ── Enterprise quote request ──────────────────────────────────────────────

    public function requestQuote(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:100'],
            'seats_needed' => ['required', 'integer', 'min:2', 'max:500'],
            'phone'        => ['nullable', 'string', 'max:30'],
            'message'      => ['nullable', 'string', 'max:1000'],
        ]);

        $user = $request->user();

        $body = "<p>Nouvelle demande de devis Pack Entreprise :</p>
<ul>
<li><strong>Entreprise :</strong> {$data['company_name']}</li>
<li><strong>Utilisateurs souhaités :</strong> {$data['seats_needed']} licences</li>
<li><strong>Demandeur :</strong> {$user->first_name} {$user->last_name} ({$user->email})</li>
<li><strong>Téléphone :</strong> " . ($data['phone'] ?: '—') . "</li>
<li><strong>Message :</strong> " . nl2br(htmlspecialchars($data['message'] ?? '')) . "</li>
</ul>
<p><a href=\"" . route('admin.users.show', $user) . "\">Voir le profil dans l'administration →</a></p>";

        try {
            $adminEmail = env('ADMIN_EMAIL', config('mail.from.address'));
            Mail::to($adminEmail)->send(new SystemNotificationMail(
                recipientName: 'Équipe LeadXchange',
                title:         'Demande de devis Pack Entreprise — ' . $data['company_name'],
                body:          $body,
                actionLabel:   'Créer la licence',
                actionUrl:     route('admin.super.enterprise.create'),
            ));
        } catch (\Exception $e) {
            Log::warning('Enterprise quote request email failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }

        return back()->with('enterprise_quote_sent', true);
    }

    // ── Join flow (public, tokenised) ─────────────────────────────────────────

    public function join(string $token)
    {
        $invitation = EnterpriseInvitation::with('license.holder')
            ->where('token', $token)
            ->whereIn('status', [EnterpriseInvitation::STATUS_PENDING, EnterpriseInvitation::STATUS_AVAILABLE])
            ->firstOrFail();

        $existingUser = $invitation->user ?? ($invitation->email ? User::where('email', $invitation->email)->first() : null);

        // Existing user with a pending invite → confirmation page (no registration needed)
        $confirmOnly = ($existingUser && $invitation->status === EnterpriseInvitation::STATUS_PENDING && $invitation->user_id);

        return view('enterprise.join', compact('invitation', 'existingUser', 'confirmOnly'));
    }

    public function processJoin(Request $request, string $token)
    {
        $invitation = EnterpriseInvitation::with('license')
            ->where('token', $token)
            ->whereIn('status', [EnterpriseInvitation::STATUS_PENDING, EnterpriseInvitation::STATUS_AVAILABLE])
            ->firstOrFail();

        $wasAvailable = $invitation->status === EnterpriseInvitation::STATUS_AVAILABLE;

        // ── Case 1: available slot (no email pre-set) — full registration form ──
        if ($wasAvailable) {
            $request->validate([
                'email'      => ['required', 'email', 'max:255'],
                'first_name' => ['required', 'string', 'max:80'],
                'last_name'  => ['required', 'string', 'max:80'],
                'password'   => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $email      = strtolower(trim($request->email));
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

        // ── Case 2: pending invitation — existing user, just confirm ──
        } elseif ($invitation->user_id && $invitation->user) {
            $targetUser = $invitation->user;

            $invitation->update([
                'status'      => EnterpriseInvitation::STATUS_ACTIVE,
                'accepted_at' => now(),
            ]);

        // ── Case 3: pending invitation — new user, registration form ──
        } else {
            $request->validate([
                'first_name' => ['required', 'string', 'max:80'],
                'last_name'  => ['required', 'string', 'max:80'],
                'password'   => ['required', 'string', 'min:8', 'confirmed'],
            ]);

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

    private function sendInvitationEmail(EnterpriseInvitation $invitation, EnterpriseLicense $license): void
    {
        try {
            $license->loadMissing('holder');
            $holderName = trim(($license->holder?->first_name ?? '') . ' ' . ($license->holder?->last_name ?? '')) ?: 'LeadXchange';
            $company    = $license->company_name ?: $holderName;

            \App\Jobs\SendQueuedEmailJob::dispatch(
                to:       $invitation->email,
                subject:  $company . ' vous invite à rejoindre son équipe LeadXchange',
                type:     'enterprise_invitation',
                mailable: new \App\Mail\EnterpriseInvitationMail($invitation, $holderName),
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
