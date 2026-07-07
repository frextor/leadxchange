<?php

namespace App\Http\Controllers\Ambassador\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait ScopesAmbassadorRegion
{
    protected function regionScope(User $ambassador): array
    {
        return [
            'region_id' => $ambassador->region_id,
            'city_id'   => $ambassador->city_id,
        ];
    }

    protected function applyRegionScope(Builder $query, User $ambassador, string $table = ''): Builder
    {
        $col = fn (string $c) => $table ? "{$table}.{$c}" : $c;

        if ($ambassador->region_id) {
            return $query->where($col('region_id'), $ambassador->region_id);
        }

        return $query->where($col('city_id'), $ambassador->city_id);
    }

    protected function isSameRegion(User $ambassador, User $target): bool
    {
        if ($ambassador->region_id) {
            return $target->region_id === $ambassador->region_id;
        }
        return $target->city_id === $ambassador->city_id;
    }

    protected function regionName(User $ambassador): string
    {
        return $ambassador->region?->name ?? $ambassador->city?->name ?? 'votre région';
    }
}
