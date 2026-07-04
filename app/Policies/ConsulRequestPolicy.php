<?php

namespace App\Policies;

use App\Models\User;
class ConsulRequestPolicy
{

    /** Can the user submit an ambassador request? Must be Consul, not yet Ambassador, no pending request. */
    public function create(User $user): bool
    {
        return $user->isConsul()
            && ! $user->isAmbassador()
            && ! $user->hasPendingAmbassadorRequest();
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
