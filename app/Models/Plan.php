<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', 'label', 'description',
        'price', 'billing_period', 'stripe_product_id', 'stripe_price_id',
        'max_leads', 'max_groups',
        'features', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'features'   => 'array',
        'price'      => 'decimal:2',
        'max_leads'  => 'integer',
        'max_groups' => 'integer',
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the subscriptions for the plan.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get active subscriptions for the plan.
     */
    public function activeSubscriptions()
    {
        return $this->hasMany(Subscription::class)->where('status', 'active');
    }
}
