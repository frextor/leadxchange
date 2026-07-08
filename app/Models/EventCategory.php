<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class EventCategory extends Model
{
    protected $fillable = ['key', 'label', 'sort_order'];

    public static function allKeyed(): array
    {
        return Cache::remember('event_categories', 3600, fn () =>
            static::orderBy('sort_order')->orderBy('label')
                  ->pluck('label', 'key')
                  ->toArray()
        );
    }

    public static function clearCache(): void
    {
        Cache::forget('event_categories');
    }
}
