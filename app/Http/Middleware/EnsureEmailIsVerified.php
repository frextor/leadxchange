<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->hasVerifiedEmail()) {
            return $next($request);
        }

        // Allow: logout, resend (in auth group), own profile
        if ($request->routeIs('logout', 'verification.send', 'verification.verify')) {
            return $next($request);
        }

        // Allow profile.me (redirects to own profile)
        if ($request->routeIs('profile.me')) {
            return $next($request);
        }

        // Allow own profile page only
        if ($request->routeIs('profile.show') && (string) $request->route('id') === (string) $user->id) {
            return $next($request);
        }

        return redirect()->route('profile.show', $user->id)
            ->with('email_unverified', true);
    }
}
