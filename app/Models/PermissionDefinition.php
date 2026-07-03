<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionDefinition extends Model
{
    protected $fillable = ['key', 'label', 'category', 'type', 'null_label', 'sort_order'];

    /** Return the display label for a given permission key (request-scoped cache). */
    public static function labelFor(string $key): ?string
    {
        static $map = null;
        if ($map === null) {
            $map = static::pluck('label', 'key')->toArray();
        }
        return $map[$key] ?? null;
    }

    /** All definitions grouped by category, ordered. */
    public static function grouped(): \Illuminate\Support\Collection
    {
        return static::orderBy('sort_order')->get()->groupBy('category');
    }
}
