<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsAmbassador
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (! $user || (! $user->isAmbassador() && ! $user->isConsul())) {
            abort(403, 'Accès réservé aux Ambassadeurs et Consuls.');
        }

        return $next($request);
    }
}
