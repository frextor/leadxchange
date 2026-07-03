<?php

namespace App\Policies;

use App\Models\User;
use App\Services\ConsulService;

class ConsulRequestPolicy
{
    public function __construct(private ConsulService $service) {}

    /** Can the user submit a consul request? Requires the Ambassadeur plan/status. */
    public function create(User $user): bool
    {
        return $this->service->hasAmbassadeurAccess($user)
            && ! $user->isConsul()
            && ! $user->consulRequests()->where('status', 'pending')->exists();
    }

    /** Can the user validate (approve/reject) consul requests? */
    public function validate(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin() || $user->isAmbassador();
    }

    /** Can the user promote to Ambassador? Admin only. */
    public function promoteAmbassador(User $user): bool
    {
        return $user->isAdmin() || $user->isSuperAdmin();
    }
}
