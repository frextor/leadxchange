<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromoCode extends Model
{
    protected $fillable = ['code', 'premium_days', 'label', 'is_active', 'created_by', 'used_by', 'used_at'];

    protected $casts = [
        'is_active' => 'boolean',
        'used_at'   => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    public function isUsed(): bool
    {
        return $this->used_by !== null;
    }

    public function isRedeemable(): bool
    {
        return $this->is_active && ! $this->isUsed();
    }

    /** Génère un code unique, lisible (pas de caractères ambigus 0/O, 1/I). */
    public static function generateUniqueCode(): string
    {
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        do {
            $code = collect(range(1, 8))->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])->implode('');
        } while (static::where('code', $code)->exists());

        return $code;
    }
}
