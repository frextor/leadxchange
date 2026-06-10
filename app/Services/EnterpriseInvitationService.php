<?php

namespace App\Services;

use App\Mail\EnterpriseInvitationMail;
use App\Models\EnterpriseInvitation;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EnterpriseInvitationService
{
    public function activeEnterpriseSubscription(User $owner): ?Subscription
    {
        return $owner->subscriptions()
            ->with('plan')
            ->where('status', 'active')
            ->whereNotNull('stripe_subscription_id')
            ->latest()
            ->get()
            ->first(fn(Subscription $subscription) => ($subscription->plan?->max_users ?? 1) > 1);
    }

    public function seatsUsed(Subscription $subscription): int
    {
        return 1 + EnterpriseInvitation::where('subscription_id', $subscription->id)
            ->where('status', 'accepted')
            ->count();
    }

    public function seatsLimit(Subscription $subscription): int
    {
        return max(1, (int) ($subscription->plan?->max_users ?? 1));
    }

    public function seatsRemaining(Subscription $subscription): int
    {
        return max(0, $this->seatsLimit($subscription) - $this->seatsUsed($subscription));
    }

    public function invite(User $owner, string $email): EnterpriseInvitation
    {
        $subscription = $this->activeEnterpriseSubscription($owner);

        if (!$subscription) {
            throw new \InvalidArgumentException('An active enterprise subscription is required to invite users.');
        }

        $email = Str::lower(trim($email));

        return DB::transaction(function () use ($owner, $subscription, $email) {
            $existingAccepted = EnterpriseInvitation::where('subscription_id', $subscription->id)
                ->where('email', $email)
                ->where('status', 'accepted')
                ->first();

            if ($existingAccepted) {
                throw new \InvalidArgumentException('This user is already using an enterprise seat.');
            }

            $pendingCount = EnterpriseInvitation::where('subscription_id', $subscription->id)
                ->where('status', 'pending')
                ->where('expires_at', '>', now())
                ->count();

            if ($this->seatsUsed($subscription) + $pendingCount >= $this->seatsLimit($subscription)) {
                throw new \InvalidArgumentException('No enterprise seats are available.');
            }

            $plainToken = Str::random(64);

            $invitation = EnterpriseInvitation::updateOrCreate(
                [
                    'subscription_id' => $subscription->id,
                    'email' => $email,
                    'status' => 'pending',
                ],
                [
                    'owner_id' => $owner->id,
                    'accepted_user_id' => null,
                    'token_hash' => hash('sha256', $plainToken),
                    'expires_at' => now()->addDays(14),
                    'accepted_at' => null,
                ],
            );

            Mail::to($email)->send(new EnterpriseInvitationMail($invitation->fresh(['owner', 'subscription.plan']), $plainToken));

            return $invitation->fresh(['acceptedUser', 'subscription.plan']);
        });
    }

    public function findValidByToken(string $token): EnterpriseInvitation
    {
        $invitation = EnterpriseInvitation::with(['owner.company', 'subscription.plan'])
            ->where('token_hash', hash('sha256', $token))
            ->where('status', 'pending')
            ->first();

        if (!$invitation || !$invitation->expires_at?->isFuture()) {
            throw new \InvalidArgumentException('This invitation is invalid or expired.');
        }

        return $invitation;
    }

    public function assertTokenCanBeAcceptedByEmail(string $token, string $email): EnterpriseInvitation
    {
        $invitation = $this->findValidByToken($token);

        if (Str::lower(trim($email)) !== Str::lower($invitation->email)) {
            throw new \InvalidArgumentException('This invitation was sent to another email address.');
        }

        if ($this->seatsRemaining($invitation->subscription) <= 0) {
            throw new \InvalidArgumentException('No enterprise seats are available.');
        }

        return $invitation;
    }

    public function acceptForUser(string $token, User $user): EnterpriseInvitation
    {
        return DB::transaction(function () use ($token, $user) {
            $invitation = $this->assertTokenCanBeAcceptedByEmail($token, $user->email);

            Subscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->update(['status' => 'canceled', 'ends_at' => now()]);

            Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $invitation->subscription->plan_id,
                'status' => 'active',
                'current_period_end' => $invitation->subscription->current_period_end,
            ]);

            $invitation->update([
                'accepted_user_id' => $user->id,
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);

            return $invitation->fresh(['owner', 'acceptedUser', 'subscription.plan']);
        });
    }
}
