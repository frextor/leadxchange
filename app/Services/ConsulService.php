<?php

namespace App\Services;

use App\Models\ConsulRequest;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ConsulService
{
    // Hierarchy: Basic → Premium → Consul (admin appoints) → Ambassadeur (consul requests)

    /** Check if a user has a paid subscription (eligible for consul nomination). */
    public function hasPremiumAccess(User $user): bool
    {
        return $user->subscription?->status === 'active'
            && (float) ($user->subscription->plan?->price ?? 0) > 0;
    }

    /** Nominate a user as Consul (admin action, direct appointment). */
    public function nominateConsul(User $user, User $admin): void
    {
        if (! $this->hasPremiumAccess($user)) {
            throw new \RuntimeException('L\'utilisateur doit avoir un abonnement payant pour devenir Consul.');
        }

        if ($user->isConsul()) {
            throw new \RuntimeException('Cet utilisateur est déjà Consul.');
        }

        $consulPlan = \App\Models\Plan::where('name', 'consul')->first();

        DB::transaction(function () use ($user, $admin, $consulPlan) {
            $user->update([
                'consul_status'      => 'approved',
                'consul_nominated_at'=> now(),
                'consul_nominated_by'=> $admin->id,
            ]);

            if ($consulPlan) {
                \App\Models\Subscription::updateOrCreate(
                    ['user_id' => $user->id],
                    ['plan_id' => $consulPlan->id, 'status' => 'active']
                );
            }
        });

        try {
            Notification::storeForUser(
                $user,
                'consul_nominated',
                'Vous êtes maintenant Consul',
                'Félicitations ! Vous avez été nommé Consul par l\'administration.',
                ['url' => route('dashboard')]
            );
        } catch (\Throwable) {}
    }

    /** Revoke Consul status and downgrade to Premium plan. */
    public function revokeConsul(User $user): void
    {
        if (! $user->isConsul()) {
            throw new \RuntimeException('Cet utilisateur n\'est pas Consul.');
        }

        // Also revoke ambassador if they had it
        if ($user->isAmbassador()) {
            $this->revokeAmbassador($user);
        }

        $premiumPlan = \App\Models\Plan::where('name', 'premium')->first();

        DB::transaction(function () use ($user, $premiumPlan) {
            $user->update([
                'consul_status'       => null,
                'consul_nominated_at' => null,
                'consul_nominated_by' => null,
            ]);

            if ($premiumPlan) {
                \App\Models\Subscription::where('user_id', $user->id)
                    ->update(['plan_id' => $premiumPlan->id, 'status' => 'active']);
            }
        });
    }

    /** Directly nominate a Premium user as Ambassador (admin action, no consul prerequisite). */
    public function nominateAmbassador(User $user, User $admin): void
    {
        if (! $this->hasPremiumAccess($user)) {
            throw new \RuntimeException('L\'utilisateur doit avoir un abonnement payant pour devenir Ambassadeur.');
        }

        if ($user->isAmbassador()) {
            throw new \RuntimeException('Cet utilisateur est déjà Ambassadeur.');
        }

        $ambassadeurPlan = \App\Models\Plan::where('name', 'ambassadeur')->first();

        DB::transaction(function () use ($user, $admin, $ambassadeurPlan) {
            $user->update([
                'ambassador_status'      => 'approved',
                'ambassador_reviewed_at' => now(),
                'ambassador_reviewed_by' => $admin->id,
            ]);

            if ($ambassadeurPlan) {
                \App\Models\Subscription::updateOrCreate(
                    ['user_id' => $user->id],
                    ['plan_id' => $ambassadeurPlan->id, 'status' => 'active']
                );
            }
        });

        try {
            Notification::storeForUser(
                $user,
                'ambassador_nominated',
                'Vous êtes maintenant Ambassadeur',
                'Félicitations ! Vous avez été nommé Ambassadeur par l\'administration.',
                ['url' => route('dashboard')]
            );
        } catch (\Throwable) {}
    }

    /** Submit an ambassador request (consul → ambassador). */
    public function request(User $user): ConsulRequest
    {
        if (! $user->isConsul()) {
            throw new \RuntimeException('Vous devez être Consul pour demander le rôle Ambassadeur.');
        }

        if ($user->isAmbassador()) {
            throw new \RuntimeException('Vous êtes déjà Ambassadeur.');
        }

        if ($user->hasPendingAmbassadorRequest()) {
            throw new \RuntimeException('Vous avez déjà une demande en attente.');
        }

        $consulRequest = ConsulRequest::create([
            'user_id' => $user->id,
            'status'  => ConsulRequest::STATUS_PENDING,
        ]);

        // Notify admins
        $admins = User::whereIn('role', ['admin', 'super_admin'])->get();
        foreach ($admins as $admin) {
            try {
                Notification::storeForUser(
                    $admin,
                    'ambassador_request_submitted',
                    'Nouvelle demande Ambassadeur',
                    "{$user->first_name} {$user->last_name} (Consul) demande le rôle Ambassadeur.",
                    ['url' => route('admin.super.ambassadors.manage'), 'user_id' => $user->id]
                );
            } catch (\Throwable) {}
        }

        return $consulRequest;
    }

    /** Approve an ambassador request (consul becomes ambassador). */
    public function approve(ConsulRequest $consulRequest, User $validator): void
    {
        if (! $consulRequest->isPending()) {
            throw new \RuntimeException('Cette demande n\'est plus en attente.');
        }

        if (! $consulRequest->user->isConsul()) {
            throw new \RuntimeException('L\'utilisateur n\'est plus Consul.');
        }

        $ambassadeurPlan = \App\Models\Plan::where('name', 'ambassadeur')->first();

        DB::transaction(function () use ($consulRequest, $validator, $ambassadeurPlan) {
            $consulRequest->update([
                'status'       => ConsulRequest::STATUS_APPROVED,
                'validated_by' => $validator->id,
                'validated_at' => now(),
            ]);

            $consulRequest->user->update([
                'ambassador_status'      => 'approved',
                'ambassador_reviewed_at' => now(),
                'ambassador_reviewed_by' => $validator->id,
            ]);

            if ($ambassadeurPlan) {
                \App\Models\Subscription::updateOrCreate(
                    ['user_id' => $consulRequest->user_id],
                    ['plan_id' => $ambassadeurPlan->id, 'status' => 'active']
                );
            }
        });

        try {
            Notification::storeForUser(
                $consulRequest->user,
                'ambassador_request_approved',
                'Demande Ambassadeur approuvée',
                'Félicitations ! Votre demande de rôle Ambassadeur a été approuvée.',
                ['url' => route('dashboard')]
            );
        } catch (\Throwable) {}
    }

    /** Reject an ambassador request. */
    public function reject(ConsulRequest $consulRequest, User $validator, ?string $reason = null): void
    {
        if (! $consulRequest->isPending()) {
            throw new \RuntimeException('Cette demande n\'est plus en attente.');
        }

        $consulRequest->update([
            'status'           => ConsulRequest::STATUS_REJECTED,
            'validated_by'     => $validator->id,
            'validated_at'     => now(),
            'rejection_reason' => $reason,
        ]);

        try {
            Notification::storeForUser(
                $consulRequest->user,
                'ambassador_request_rejected',
                'Demande Ambassadeur refusée',
                'Votre demande de rôle Ambassadeur a été refusée.' . ($reason ? ' Raison : ' . $reason : ''),
                ['url' => route('dashboard')]
            );
        } catch (\Throwable) {}
    }

    /** Revoke Ambassador status — downgrades to Consul if user is Consul, otherwise to Premium. */
    public function revokeAmbassador(User $user): void
    {
        if (! $user->isAmbassador()) {
            throw new \RuntimeException('Cet utilisateur n\'est pas Ambassadeur.');
        }

        $fallbackPlan = $user->isConsul()
            ? \App\Models\Plan::where('name', 'consul')->first()
            : \App\Models\Plan::where('name', 'premium')->first();

        DB::transaction(function () use ($user, $fallbackPlan) {
            $user->update([
                'ambassador_status'      => null,
                'ambassador_reviewed_at' => now(),
                'ambassador_reviewed_by' => auth()->id(),
            ]);

            $user->consulRequests()->where('status', 'approved')->update(['status' => 'revoked']);

            if ($fallbackPlan) {
                \App\Models\Subscription::where('user_id', $user->id)
                    ->update(['plan_id' => $fallbackPlan->id, 'status' => 'active']);
            }
        });
    }

    /** @deprecated Use nominateConsul() */
    public function promoteAmbassador(User $user, User $admin): void
    {
        $this->nominateConsul($user, $admin);
    }
}
