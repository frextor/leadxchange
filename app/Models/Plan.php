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
        'name', 'label', 'description', 'price', 'billing_period',
        'stripe_product_id', 'stripe_price_id',
        'max_leads', 'max_groups', 'max_users',
        'features', 'permissions',
        'is_active', 'is_visible', 'is_enterprise',
        'contact_cta', 'sort_order',
    ];

    protected $casts = [
        'features'      => 'array',
        'permissions'   => 'array',
        'price'         => 'decimal:2',
        'max_leads'     => 'integer',
        'max_groups'    => 'integer',
        'max_users'     => 'integer',
        'is_active'     => 'boolean',
        'is_visible'    => 'boolean',
        'is_enterprise' => 'boolean',
        'sort_order'    => 'integer',
    ];

    /** Check if this plan grants a given permission key. */
    public function can(string $permission): bool
    {
        return (bool) ($this->permissions[$permission] ?? false);
    }

    /** Get a permission value (useful for numeric limits like mail_reply_weekly_limit). */
    public function permission(string $key, mixed $default = null): mixed
    {
        return $this->permissions[$key] ?? $default;
    }

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
