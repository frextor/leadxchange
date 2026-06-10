<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnterpriseInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'subscription_id',
        'accepted_user_id',
        'email',
        'token_hash',
        'status',
        'expires_at',
        'accepted_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function acceptedUser()
    {
        return $this->belongsTo(User::class, 'accepted_user_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' && $this->expires_at?->isFuture();
    }
}
