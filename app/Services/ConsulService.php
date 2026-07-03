<?php

namespace App\Services;

use App\Models\ConsulRequest;
use App\Models\User;
use App\Notifications\ConsulRequestApproved;
use App\Notifications\ConsulRequestRejected;
use App\Notifications\ConsulRequestSubmitted;
use Illuminate\Support\Facades\DB;

class ConsulService
{
    /** Check if a user has an active paid subscription (eligible for ambassador promotion). */
    public function hasPremiumAccess(User $user): bool
    {
        return $user->subscription?->status === 'active'
            && (float) ($user->subscription->plan?->price ?? 0) > 0;
    }

    /** Check if a user has the Ambassadeur plan or status (required for consul request). */
    public function hasAmbassadeurAccess(User $user): bool
    {
        return $user->isAmbassador()
            || $user->subscription?->plan?->name === 'ambassadeur';
    }

    /** Submit a consul request. */
    public function request(User $user): ConsulRequest
    {
        if (! $this->hasAmbassadeurAccess($user)) {
            throw new \RuntimeException('Vous devez avoir le plan Ambassadeur pour demander le rôle Consul.');
        }

        if ($user->consulRequests()->where('status', 'pending')->exists()) {
            throw new \RuntimeException('Vous avez déjà une demande en attente.');
        }

        if ($user->isConsul()) {
            throw new \RuntimeException('Vous êtes déjà Consul.');
        }

        $consulRequest = ConsulRequest::create([
            'user_id' => $user->id,
            'status'  => ConsulRequest::STATUS_PENDING,
        ]);

        // Notify admins and ambassadors
        $notifiables = User::where(fn($q) =>
            $q->where('role', 'admin')
              ->orWhere('role', 'super_admin')
              ->orWhere('ambassador_status', 'approved')
        )->get();

        foreach ($notifiables as $notifiable) {
            try {
                $notifiable->notify(new ConsulRequestSubmitted($consulRequest));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('ConsulRequestSubmitted notification failed', [
                    'notifiable_id' => $notifiable->id,
                    'error'         => $e->getMessage(),
                ]);
            }
        }

        return $consulRequest;
    }

    /** Approve a consul request. */
    public function approve(ConsulRequest $consulRequest, User $validator): void
    {
        if (! $consulRequest->isPending()) {
            throw new \RuntimeException('Cette demande n\'est plus en attente.');
        }

        if (! $this->hasPremiumAccess($consulRequest->user)) {
            throw new \RuntimeException('L\'utilisateur n\'a plus d\'abonnement actif.');
        }

        DB::transaction(function () use ($consulRequest, $validator) {
            $consulRequest->update([
                'status'       => ConsulRequest::STATUS_APPROVED,
                'validated_by' => $validator->id,
                'validated_at' => now(),
            ]);

            // Bascule le plan vers "consul" sans paiement
            $consulPlan = \App\Models\Plan::where('name', 'consul')->first();
            if ($consulPlan) {
                \App\Models\Subscription::updateOrCreate(
                    ['user_id' => $consulRequest->user_id],
                    ['plan_id' => $consulPlan->id, 'status' => 'active']
                );
            }

            $consulRequest->user->notify(new ConsulRequestApproved());
        });
    }

    /** Reject a consul request. */
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

        $consulRequest->user->notify(new ConsulRequestRejected($reason));
    }

    /** Promote a user to Ambassador (admin/super_admin only).
     *  Requires an active paid subscription. Changes the plan to "ambassadeur".
     */
    public function promoteAmbassador(User $user, User $admin): void
    {
        if (! $this->hasPremiumAccess($user)) {
            throw new \RuntimeException('L\'utilisateur doit avoir un abonnement payant (non Basic) pour devenir Ambassadeur.');
        }

        if ($user->isAmbassador()) {
            throw new \RuntimeException('Cet utilisateur est déjà Ambassadeur.');
        }

        $ambassadeurPlan = \App\Models\Plan::where('name', 'ambassadeur')->first();

        DB::transaction(function () use ($user, $admin, $ambassadeurPlan) {
            // Update ambassador status
            $user->update([
                'ambassador_status'      => 'approved',
                'ambassador_reviewed_at' => now(),
                'ambassador_reviewed_by' => $admin->id,
            ]);

            // Upgrade subscription plan to "ambassadeur" if the plan exists
            if ($ambassadeurPlan) {
                \App\Models\Subscription::updateOrCreate(
                    ['user_id' => $user->id],
                    ['plan_id' => $ambassadeurPlan->id, 'status' => 'active']
                );
            }
        });
    }

    /** Revoke Ambassador role and downgrade plan to Prémium. */
    public function revokeAmbassador(User $user): void
    {
        if (! $user->isAmbassador()) {
            throw new \RuntimeException('Cet utilisateur n\'est pas Ambassadeur.');
        }

        $premiumPlan = \App\Models\Plan::where('name', 'premium')->first();

        DB::transaction(function () use ($user, $premiumPlan) {
            $user->update([
                'ambassador_status'      => null,
                'ambassador_reviewed_at' => now(),
                'ambassador_reviewed_by' => auth()->id(),
            ]);

            // Downgrade to Prémium if that plan exists
            if ($premiumPlan) {
                \App\Models\Subscription::where('user_id', $user->id)
                    ->update(['plan_id' => $premiumPlan->id, 'status' => 'active']);
            }
        });
    }
}
