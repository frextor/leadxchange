<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

trait EnforcePlanLimits
{
    /**
     * Return an upgrade redirect if the user lacks a permission.
     * Returns null if the user CAN do the action.
     */
    protected function requirePermission(string $permission, string $message): ?RedirectResponse
    {
        if (! Auth::user()->canFeature($permission)) {
            return $this->upgradeDenied($message);
        }
        return null;
    }

    protected function upgradeDenied(string $message): RedirectResponse
    {
        return redirect()->route('upgrade')->with('upgrade_reason', $message);
    }
}
