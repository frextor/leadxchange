<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait EnforcePlanLimits
{
    /**
     * Return an upgrade redirect/JSON error if the user lacks a permission.
     * Returns null if the user CAN do the action.
     */
    protected function requirePermission(string $permission, string $message = ''): RedirectResponse|JsonResponse|null
    {
        if (! Auth::user()->canFeature($permission)) {
            $msg = $message ?: 'Votre abonnement ne vous permet pas d\'effectuer cette action. Veuillez upgrader votre plan.';
            if (Request::expectsJson()) {
                return response()->json(['message' => $msg], 403);
            }
            return back()->with('upgrade_feature', $permission);
        }
        return null;
    }
}
