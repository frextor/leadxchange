<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsAmbassador
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! auth()->check() || ! auth()->user()->isAmbassador()) {
            abort(403, 'Accès réservé aux Ambassadeurs.');
        }

        return $next($request);
    }
}
