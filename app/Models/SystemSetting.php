<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value', 'type', 'group', 'description'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $settings = Cache::remember('system_settings', 3600, fn () =>
            static::all()->keyBy('key')
        );

        $setting = $settings->get($key);

        if (!$setting) {
            return $default;
        }

        return match ($setting->type) {
            'int'   => (int) $setting->value,
            'float' => (float) $setting->value,
            'bool'  => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            default => $setting->value,
        };
    }

    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        Cache::forget('system_settings');
    }
}
