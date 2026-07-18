<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BalancePurchase extends Model
{
    protected $fillable = [
        'user_id',
        'stripe_payment_intent_id',
        'points',
        'amount_cents',
        'currency',
        'status',
        'failure_message',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
