<?php

namespace App\Services;

use App\Mail\EnterpriseInvitationMail;
use App\Models\EnterpriseInvitation;
use App\Models\EnterpriseLicense;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EnterpriseInvitationService
{
    /** Find the active, non-expired license held by this user. */
    public function licenseForHolder(User $holder): ?EnterpriseLicense
    {
        return EnterpriseLicense::where('holder_user_id', $holder->id)
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->with('plan')
            ->first();
    }

    /** Seat counters. */
    public function seatsInfo(EnterpriseLicense $license): array
    {
        return [
            'used'      => $license->seats_used,
            'total'     => $license->seats_total,
            'remaining' => max(0, $license->seats_total - $license->seats_used),
        ];
    }

    /**
     * Claim an available slot and send an invitation email.
     * Mobile mirror of EnterpriseController::invite().
     */
    public function invite(User $holder, string $email): EnterpriseInvitation
    {
        $license = $this->licenseForHolder($holder);
        if (! $license) {
            throw new \InvalidArgumentException('Vous n\'avez pas de licence entreprise active.');
        }

        $email = Str::lower(trim($email));

        if ($email === Str::lower($holder->email)) {
            throw new \InvalidArgumentException('Vous ne pouvez pas vous inviter vous-même.');
        }

        $existing = $license->invitations()
            ->whereNotNull('email')
            ->where('email', $email)
            ->whereIn('status', [EnterpriseInvitation::STATUS_PENDING, EnterpriseInvitation::STATUS_ACTIVE])
            ->first();

        if ($existing) {
            throw new \InvalidArgumentException("{$email} possède déjà une invitation active ou en cours.");
        }

        $slot = $license->invitations()
            ->where('status', EnterpriseInvitation::STATUS_AVAILABLE)
            ->first();

        if (! $slot) {
            throw new \InvalidArgumentException('Aucune licence disponible. Augmentez votre quota ou révoquez un membre inactif.');
        }

        return DB::transaction(function () use ($holder, $license, $email, $slot) {
            $targetUser = User::where('email', $email)->first();

            $slot->update([
                'email'      => $email,
                'user_id'    => $targetUser?->id,
                'status'     => EnterpriseInvitation::STATUS_PENDING,
                'invited_by' => $holder->id,
                'token'      => EnterpriseInvitation::generateToken(),
            ]);

            $license->increment('seats_used');
            $this->sendInvitationEmail($slot->fresh(), $license);

            return $slot->fresh(['license', 'user']);
        });
    }

    /** Find a pending or available invitation by its token. */
    public function findValidByToken(string $token): EnterpriseInvitation
    {
        $invitation = EnterpriseInvitation::with(['license.holder', 'license.plan', 'user'])
            ->where('token', $token)
            ->whereIn('status', [EnterpriseInvitation::STATUS_PENDING, EnterpriseInvitation::STATUS_AVAILABLE])
            ->first();

        if (! $invitation) {
            throw new \InvalidArgumentException('This invitation is invalid or has already been used.');
        }

        if ($invitation->license->isExpired()) {
            throw new \InvalidArgumentException('This enterprise license has expired.');
        }

        return $invitation;
    }

    /**
     * Validate that a token can be accepted by a given email before account creation.
     * - Available slots: no email restriction (any email accepted)
     * - Pending invites: email must match the one on the invitation
     */
    public function assertTokenCanBeAcceptedByEmail(string $token, string $email): EnterpriseInvitation
    {
        $invitation = $this->findValidByToken($token);

        if (
            $invitation->status === EnterpriseInvitation::STATUS_PENDING
            && $invitation->email !== null
            && Str::lower(trim($email)) !== Str::lower($invitation->email)
        ) {
            throw new \InvalidArgumentException('This invitation was sent to another email address.');
        }

        return $invitation;
    }

    /**
     * Accept the invitation for an existing user: grant enterprise plan, mark slot active.
     * Mirror of EnterpriseController::processJoin() for the API.
     */
    public function acceptForUser(string $token, User $user): EnterpriseInvitation
    {
        return DB::transaction(function () use ($token, $user) {
            $invitation = $this->findValidByToken($token);

            // Re-check email for pending invites (available slots accept any user)
            if (
                $invitation->status === EnterpriseInvitation::STATUS_PENDING
                && $invitation->email !== null
                && Str::lower($user->email) !== Str::lower($invitation->email)
            ) {
                throw new \InvalidArgumentException('This invitation was sent to another email address.');
            }

            $wasAvailable = $invitation->status === EnterpriseInvitation::STATUS_AVAILABLE;

            $invitation->update([
                'user_id'     => $user->id,
                'email'       => $invitation->email ?? $user->email,
                'status'      => EnterpriseInvitation::STATUS_ACTIVE,
                'accepted_at' => now(),
            ]);

            // seats_used was already incremented when the invite was sent (pending).
            // For available slots that are accepted directly, count the seat now.
            if ($wasAvailable) {
                $invitation->license->increment('seats_used');
            }

            // Grant the enterprise plan
            Subscription::where('user_id', $user->id)
                ->update(['status' => 'canceled', 'ends_at' => now()]);

            Subscription::create([
                'user_id'            => $user->id,
                'plan_id'            => $invitation->license->plan_id,
                'status'             => 'active',
                'current_period_end' => $invitation->license->expires_at,
            ]);

            return $invitation->fresh(['license.plan', 'license.holder', 'user']);
        });
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
                mailable: new EnterpriseInvitationMail($invitation, $holderName),
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
