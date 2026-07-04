<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EnterpriseInvitation extends Model
{
    const STATUS_AVAILABLE = 'available';
    const STATUS_PENDING   = 'pending';
    const STATUS_ACTIVE    = 'active';
    const STATUS_REVOKED   = 'revoked';

    protected $fillable = [
        'license_id', 'invited_by', 'email', 'user_id', 'status', 'token', 'accepted_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    public function license(): BelongsTo
    {
        return $this->belongsTo(EnterpriseLicense::class);
    }

    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generateToken(): string
    {
        return Str::random(48);
    }

    public function joinUrl(): string
    {
        return route('enterprise.join', $this->token);
    }
}
