<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsEnterpriseHolder
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if (! $user || ! $user->enterpriseLicense()->exists()) {
            abort(403, 'Accès réservé aux titulaires d\'un Pack Entreprise.');
        }

        return $next($request);
    }
}
