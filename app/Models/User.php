<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'gender',
        'city_birth_id',
        'city_living_id',
        'birthday',
        'phone',
        'phone_country_code',
        'nationality_id',
        'company_id',
        'position',
        'onboarding_completed',
        'newsletter',
        'notifications',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at'    => 'datetime',
        'birthday'             => 'date',
        'onboarding_completed' => 'boolean',
        'newsletter'           => 'boolean',
        'notifications'        => 'boolean',
        'password'             => 'hashed',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function cityLiving()
    {
        return $this->belongsTo(\App\Models\City::class, 'city_living_id');
    }

    public function cityBirth()
    {
        return $this->belongsTo(\App\Models\City::class, 'city_birth_id');
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function interests()
    {
        return $this->belongsToMany(Interest::class, 'user_interests');
    }

    public function groups()
    {
        return $this->belongsToMany(\App\Models\Group::class, 'group_user')
                    ->withPivot('role', 'joined_at');
    }

    public function events()
    {
        return $this->belongsToMany(\App\Models\Event::class, 'event_user')
                    ->withPivot('role', 'registered_at');
    }

    public function languages()
    {
        return $this->belongsToMany(Language::class, 'user_languages')
                    ->withPivot('level')
                    ->withTimestamps();
    }

    public function nationality()
    {
        return $this->belongsTo(Nationality::class);
    }

    /**
     * Get the user's active subscription.
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class)->where('status', 'active')->latest();
    }

    /**
     * Get all user's subscriptions.
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the user's active plan through subscription.
     */
    public function plan()
    {
        return $this->hasOneThrough(
            Plan::class,
            Subscription::class,
            'user_id',
            'id',
            'id',
            'plan_id'
        )->where('subscriptions.status', 'active')->latest();
    }

    /**
     * Check if user has completed profile.
     *
     * @return bool
     */
    public function hasCompletedProfile(): bool
    {
        return !is_null($this->gender)
            && !is_null($this->city_birth_id)
            && !is_null($this->city_living_id)
            && !is_null($this->birthday);
    }

    /**
     * Check if user is on basic plan.
     *
     * @return bool
     */
    public function isBasic(): bool
    {
        return $this->subscription && $this->subscription->plan->name === 'basic';
    }

    /**
     * Check if user is on ambassadeur plan.
     *
     * @return bool
     */
    public function isAmbassadeur(): bool
    {
        return $this->subscription && $this->subscription->plan->name === 'ambassadeur';
    }

    /**
     * Check if user is on premium gold plan.
     *
     * @return bool
     */
    public function isPremiumGold(): bool
    {
        return $this->subscription && $this->subscription->plan->name === 'premium_gold';
    }

    /**
     * Get a specific feature from the user's plan.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getFeature(string $key, $default = null)
    {
        if (!$this->subscription || !$this->subscription->plan) {
            return $default;
        }

        $features = $this->subscription->plan->features;

        return $features[$key] ?? $default;
    }

    /**
     * Get user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
