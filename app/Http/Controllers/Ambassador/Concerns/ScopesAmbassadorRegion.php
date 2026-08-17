<?php

namespace App\Http\Controllers\Ambassador\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait ScopesAmbassadorRegion
{
    /**
     * Retourne la région/ville verrouillée à la nomination de l'ambassadeur.
     * Utilise ambassador_region_id / ambassador_city_id en priorité (immuable),
     * puis region_id / city_id courants en fallback.
     */
    protected function ambassadorRegionId(User $ambassador): ?int
    {
        return $ambassador->ambassador_region_id ?? $ambassador->region_id ?? null;
    }

    protected function ambassadorCityId(User $ambassador): ?int
    {
        return $ambassador->ambassador_city_id ?? $ambassador->city_id ?? null;
    }

    protected function regionScope(User $ambassador): array
    {
        return [
            'region_id' => $this->ambassadorRegionId($ambassador),
            'city_id'   => $this->ambassadorCityId($ambassador),
        ];
    }

    protected function applyRegionScope(Builder $query, User $ambassador, string $table = ''): Builder
    {
        $col      = fn (string $c) => $table ? "{$table}.{$c}" : $c;
        $regionId = $this->ambassadorRegionId($ambassador);
        $cityId   = $this->ambassadorCityId($ambassador);

        if ($regionId) {
            return $query->where($col('region_id'), $regionId);
        }

        if ($cityId) {
            return $query->where($col('city_id'), $cityId);
        }

        // Aucun territoire défini — ne retourner aucun résultat
        return $query->whereRaw('1 = 0');
    }

    protected function isSameRegion(User $ambassador, User $target): bool
    {
        $regionId = $this->ambassadorRegionId($ambassador);
        $cityId   = $this->ambassadorCityId($ambassador);

        if (! $regionId && ! $cityId) {
            return false;
        }

        if ($regionId) {
            return (int) $target->region_id === (int) $regionId;
        }
        return (int) $target->city_id === (int) $cityId;
    }

    protected function regionName(User $ambassador): string
    {
        $regionId = $this->ambassadorRegionId($ambassador);
        $cityId   = $this->ambassadorCityId($ambassador);

        if ($regionId) {
            $region = \App\Models\Region::find($regionId);
            if ($region) return $region->name;
        }

        if ($cityId) {
            $city = \App\Models\City::find($cityId);
            if ($city) return $city->name;
        }

        return $ambassador->region?->name ?? $ambassador->city?->name ?? 'votre région';
    }
}
