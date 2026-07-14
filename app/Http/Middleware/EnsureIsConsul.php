<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsConsul
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isConsul()) {
            abort(403, 'Accès réservé aux consuls.');
        }

        return $next($request);
    }
}
