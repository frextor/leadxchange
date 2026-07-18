<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'plan_id',
        'stripe_subscription_id',
        'billing_period',
        'status',
        'stripe_status',
        'trial_ends_at',
        'ends_at',
        'current_period_end',
        'cancel_at_period_end',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'trial_ends_at' => 'datetime',
        'ends_at' => 'datetime',
        'current_period_end' => 'datetime',
        'cancel_at_period_end' => 'boolean',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the plan that owns the subscription.
     */
    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Check if subscription is active.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if subscription is canceled.
     *
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->status === 'canceled';
    }

    /**
     * Check if subscription is on trial.
     *
     * @return bool
     */
    public function onTrial(): bool
    {
        return $this->trial_ends_at && now()->lt($this->trial_ends_at);
    }
}
