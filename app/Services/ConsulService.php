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
    /** Check if a user has an active paid subscription (eligible for consul). */
    public function hasPremiumAccess(User $user): bool
    {
        return $user->subscription?->status === 'active'
            && (float) ($user->subscription->plan?->price ?? 0) > 0;
    }

    /** Submit a consul request. */
    public function request(User $user): ConsulRequest
    {
        if (! $this->hasPremiumAccess($user)) {
            throw new \RuntimeException('Un abonnement payant est requis pour demander le rôle Consul.');
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
            $notifiable->notify(new ConsulRequestSubmitted($consulRequest));
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

    /** Promote a user to Ambassador (admin only). */
    public function promoteAmbassador(User $user, User $admin): void
    {
        if (! $this->hasPremiumAccess($user)) {
            throw new \RuntimeException('L\'utilisateur doit avoir un abonnement payant pour devenir Ambassadeur.');
        }

        if ($user->isAmbassador()) {
            throw new \RuntimeException('Cet utilisateur est déjà Ambassadeur.');
        }

        $user->update([
            'ambassador_status'      => 'approved',
            'ambassador_reviewed_at' => now(),
            'ambassador_reviewed_by' => $admin->id,
        ]);
    }

    /** Revoke Ambassador role. */
    public function revokeAmbassador(User $user): void
    {
        if (! $user->isAmbassador()) {
            throw new \RuntimeException('Cet utilisateur n\'est pas Ambassadeur.');
        }

        $user->update([
            'ambassador_status'      => 'rejected',
            'ambassador_reviewed_at' => now(),
            'ambassador_reviewed_by' => auth()->id(),
        ]);
    }
}
