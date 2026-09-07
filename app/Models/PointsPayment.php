<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointsPayment extends Model
{
    protected $fillable = [
        'user_id', 'stripe_session_id', 'stripe_payment_intent_id',
        'points_purchased', 'amount_cents', 'currency', 'source', 'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getAmountAttribute(): float
    {
        return $this->amount_cents / 100;
    }
}
