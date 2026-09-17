<?php

namespace App\Http\Middleware;

use App\Models\SystemSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verrou pré-lancement : avant l'ouverture officielle de la plateforme,
 * l'inscription reste ouverte (pour constituer la liste des futurs membres)
 * mais aucune fonctionnalité du site n'est accessible — chaque utilisateur
 * connecté tombe sur une page d'attente tant que l'admin n'a pas confirmé
 * le lancement dans Admin → Paramètres → Lancement.
 */
class PreLaunchGate
{
    /** Routes/préfixes toujours accessibles, même avant le lancement */
    protected array $except = [
        'register',
        'login',
        'logout',
        'admin-access',
        'legal',
        'a-propos',
        'email',
        'forgot-password',
        'reset-password',
        'firebase-messaging-sw.js',
        'stripe',
        '_debugbar',
        'sanctum',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // Lu directement en base (pas via le cache 1h de SystemSetting::get())
        // pour que le bouton admin ait un effet immédiat.
        $row      = SystemSetting::where('key', 'platform_launched')->first();
        $launched = $row ? filter_var($row->value, FILTER_VALIDATE_BOOLEAN) : true;

        if ($launched) {
            return $next($request);
        }

        // Sous-domaine admin → toujours exempt
        if (str_starts_with($request->getHost(), 'admin.')) {
            return $next($request);
        }

        // Admins et super_admins passent toujours (pour piloter/tester avant le lancement)
        $user = $request->user();
        if ($user && in_array($user->role, ['admin', 'super_admin'])) {
            return $next($request);
        }

        // Requêtes API (mobile) → réponse JSON plutôt qu'une page HTML
        if ($request->is('api/*')) {
            return $next($request);
        }

        // Chemins publics exemptés (accueil, connexion, inscription, légal…)
        $path = ltrim($request->path(), '/');
        if ($path === '' || $path === '/') {
            return $next($request);
        }
        foreach ($this->except as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return $next($request);
            }
        }

        $rowMsg = SystemSetting::where('key', 'prelaunch_message')->first();
        $message = $rowMsg?->value ?: 'Votre inscription est confirmée. Nous vous préviendrons par email dès l\'ouverture officielle de la plateforme.';

        return response()->view('prelaunch', ['message' => $message], 200);
    }
}
