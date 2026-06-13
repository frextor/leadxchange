<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class EmailSetting extends Model
{
    protected $fillable = [
        'name', 'driver', 'host', 'port', 'encryption',
        'username', 'password', 'from_address', 'from_name', 'is_active',
    ];

    protected $hidden = ['password'];

    protected $casts = [
        'port'      => 'integer',
        'is_active' => 'boolean',
    ];

    // ── Cache ────────────────────────────────────────────────────────────────

    public static function getActive(): ?self
    {
        return Cache::remember('email_setting_active', 3600, function () {
            return self::where('is_active', true)->first();
        });
    }

    public static function clearCache(): void
    {
        Cache::forget('email_setting_active');
    }

    // ── Password encryption ───────────────────────────────────────────────────

    public function setPasswordAttribute(?string $value): void
    {
        $this->attributes['password'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getDecryptedPassword(): ?string
    {
        if (empty($this->attributes['password'])) {
            return null;
        }
        try {
            return Crypt::decryptString($this->attributes['password']);
        } catch (\Exception) {
            return null;
        }
    }

    // ── Boot ─────────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::saved(fn () => self::clearCache());
        static::deleted(fn () => self::clearCache());
    }
}
