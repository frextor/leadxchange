<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EnterpriseLicense extends Model
{
    protected $fillable = [
        'holder_user_id', 'plan_id', 'seats_total', 'seats_used', 'notes', 'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function holder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'holder_user_id');
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(EnterpriseInvitation::class, 'license_id');
    }

    public function activeInvitations(): HasMany
    {
        return $this->hasMany(EnterpriseInvitation::class, 'license_id')
                    ->whereIn('status', ['pending', 'active']);
    }

    public function seatsAvailable(): int
    {
        // holder always occupies 1 seat
        return max(0, $this->seats_total - $this->seats_used);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
