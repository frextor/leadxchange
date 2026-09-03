<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use App\Models\SystemSetting;
use Symfony\Component\HttpFoundation\Response;

class GeoBlock
{
    public function handle(Request $request, Closure $next): Response
    {
        // Sous-domaine admin → toujours exempt
        if (str_starts_with($request->getHost(), 'admin.')) {
            return $next($request);
        }

        // Routes admin toujours exemptées
        $path = ltrim($request->path(), '/');
        if ($path === 'admin' || str_starts_with($path, 'admin/') || $path === 'admin-access') {
            return $next($request);
        }

        // Admins et super_admins passent toujours
        $user = $request->user();
        if ($user && in_array($user->role, ['admin', 'super_admin'])) {
            return $next($request);
        }

        // Lire DIRECTEMENT depuis la DB (sans cache) pour avoir les données fraîches
        $rowCountries = SystemSetting::where('key', 'geo_blocked_countries')->first();
        $blocked      = ($rowCountries && $rowCountries->value)
                        ? (json_decode($rowCountries->value, true) ?? [])
                        : [];

        $rowCities    = SystemSetting::where('key', 'geo_blocked_cities')->first();
        $blockedCities = ($rowCities && $rowCities->value)
                        ? (json_decode($rowCities->value, true) ?? [])
                        : [];

        // Si rien n'est bloqué, on passe
        if (empty($blocked) && empty($blockedCities)) {
            return $next($request);
        }

        // Détecter l'IP et la géolocaliser
        $ip   = $this->getRealIp($request);
        $info = $this->getGeoInfo($ip);

        if (! $info) {
            return $next($request); // IP locale ou erreur API → on laisse passer
        }

        $countryCode = $info['countryCode'] ?? '';
        $countryName = $info['country']     ?? '';
        $regionName  = $info['regionName']  ?? '';
        $cityName    = $info['city']        ?? '';

        // Blocage par pays
        if (! empty($blocked) && in_array($countryCode, $blocked)) {
            return response()->view('geo-blocked', [
                'location' => $cityName ? "{$cityName}, {$countryName}" : $countryName,
                'reason'   => 'pays',
            ], 403);
        }

        // Blocage par région (regionName de ip-api, ex: "Île-de-France")
        // La liste $blockedCities contient en réalité des noms de régions
        if ($regionName && ! empty($blockedCities)) {
            $regionLower = mb_strtolower($regionName);
            foreach ($blockedCities as $bc) {
                if (mb_strtolower($bc) === $regionLower) {
                    $location = $cityName ? "{$cityName} ({$regionName})" : $regionName;
                    return response()->view('geo-blocked', [
                        'location' => $location,
                        'reason'   => 'région',
                    ], 403);
                }
            }
        }

        return $next($request);
    }

    /** Récupère la vraie IP (gère proxies/Cloudflare) */
    private function getRealIp(Request $request): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP'] as $header) {
            $val = $_SERVER[$header] ?? null;
            if ($val) return explode(',', $val)[0];
        }
        return $request->ip() ?? '127.0.0.1';
    }

    /** Géolocalise l'IP via ip-api.com (résultat mis en cache 6h) */
    private function getGeoInfo(string $ip): ?array
    {
        // IPs locales → pas de blocage
        if (in_array($ip, ['127.0.0.1', '::1']) || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
            return null;
        }

        $cacheKey = 'geoip_' . md5($ip);
        return Cache::remember($cacheKey, 21600, function () use ($ip) {
            try {
                $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=status,country,countryCode,regionName,city");
                if ($response->ok()) {
                    $data = $response->json();
                    return $data['status'] === 'success' ? $data : null;
                }
            } catch (\Throwable $e) {
                // Silencieux — on laisse passer en cas d'erreur
            }
            return null;
        });
    }
}
