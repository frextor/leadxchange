<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCguAccepted
{
    /**
     * Routes (by name) that are accessible without CGU acceptance.
     */
    protected array $except = [
        'cgu.accept',
        'logout',
        'legal.show',
        'legal.cgu',
        'privacy',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // Allow exempted routes through without check.
        $routeName = $request->route()?->getName() ?? '';
        foreach ($this->except as $exempt) {
            if ($routeName === $exempt || str_starts_with($routeName, $exempt)) {
                return $next($request);
            }
        }

        $currentVersion = SystemSetting::get('cgu_current_version', '1.1');

        if ($user->cgu_version !== $currentVersion) {
            // AJAX / JSON requests get a 403 with a message.
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Vous devez accepter les Conditions Générales d\'Utilisation pour continuer.',
                    'cgu_required' => true,
                ], 403);
            }

            return redirect()->route('cgu.wall');
        }

        return $next($request);
    }
}
