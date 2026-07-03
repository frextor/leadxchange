<?php

namespace App\Models;

use App\Mail\PasswordResetMail;
use App\Mail\VerificationMail;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
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
        'city_id',
        'birthday',
        'phone',
        'phone_country_code',
        'nationality_id',
        'company_id',
        'onboarding_completed',
        'newsletter',
        'notifications',
        'role',
        'region_id',
        'points_balance',
        'badge_level',
        'ambassador_status',
        'ambassador_requested_at',
        'ambassador_reviewed_at',
        'ambassador_reviewed_by',
        'ambassador_rejection_reason',
        'admin_permissions',
        'stripe_customer_id',
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
        'password'                   => 'string',
        'points_balance'             => 'integer',
        'ambassador_requested_at'    => 'datetime',
        'ambassador_reviewed_at'     => 'datetime',
        'admin_permissions'          => 'array',
    ];

    /** All available admin permissions with their French labels. */
    public const ADMIN_PERMISSIONS = [
        'manage_users'  => 'Gestion des utilisateurs',
        'manage_leads'  => 'Modération des leads',
        'manage_events' => 'Gestion des événements',
        'manage_groups' => 'Gestion des groupes',
        'manage_videos' => 'Modération des vidéos',
    ];

    /**
     * Check if this admin has a given permission.
     * Super admins always pass. Admins with null/empty permissions have all permissions.
     */
    public function hasAdminPermission(string $perm): bool
    {
        if ($this->isSuperAdmin()) return true;
        $perms = $this->admin_permissions;
        if (empty($perms)) return true; // no restriction = all permissions
        return in_array($perm, $perms);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function city()
    {
        return $this->belongsTo(\App\Models\City::class, 'city_id');
    }

    public function region()
    {
        return $this->belongsTo(\App\Models\City::class, 'region_id');
    }

    public function ambassadorReviewer()
    {
        return $this->belongsTo(User::class, 'ambassador_reviewed_by');
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /** Check if the user's active plan grants a given permission. */
    public function planCan(string $permission): bool
    {
        return $this->subscription?->plan?->can($permission) ?? false;
    }

    /** Get a numeric/value permission from the user's active plan. */
    public function planPermission(string $key, mixed $default = null): mixed
    {
        return $this->subscription?->plan?->permission($key, $default) ?? $default;
    }

    public function isAmbassador(): bool
    {
        return $this->ambassador_status === 'approved';
    }

    public function isConsul(): bool
    {
        return $this->consulRequests()->where('status', 'approved')->exists();
    }

    public function hasPendingConsulRequest(): bool
    {
        return $this->consulRequests()->where('status', 'pending')->exists();
    }

    public function consulRequests()
    {
        return $this->hasMany(\App\Models\ConsulRequest::class);
    }

    public function latestConsulRequest(): ?\App\Models\ConsulRequest
    {
        return $this->consulRequests()->latest()->first();
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

    public function sentLeads()
    {
        return $this->hasMany(\App\Models\Lead::class, 'sender_id');
    }

    public function receivedLeads()
    {
        return $this->hasMany(\App\Models\Lead::class, 'receiver_id');
    }

    public function connections()
    {
        return \App\Models\Connection::where('status', 'accepted')
            ->where(fn($q) => $q->where('sender_id', $this->id)->orWhere('receiver_id', $this->id));
    }

    public function connectionIds(): array
    {
        return \App\Models\Connection::where('status', 'accepted')
            ->where(fn($q) => $q->where('sender_id', $this->id)->orWhere('receiver_id', $this->id))
            ->get()
            ->map(fn($c) => $c->sender_id === $this->id ? $c->receiver_id : $c->sender_id)
            ->toArray();
    }

    public function isConnectedWith(int $userId): bool
    {
        return \App\Models\Connection::where('status', 'accepted')
            ->where(
                fn($q) => $q
                    ->where(['sender_id' => $this->id, 'receiver_id' => $userId])
                    ->orWhere(['sender_id' => $userId, 'receiver_id' => $this->id])
            )->exists();
    }

    public function adjustPoints(int $delta, string $reason = ''): void
    {
        $current = (int) ($this->points_balance ?? 0);
        $newBalance = $current + $delta;
        if ($delta > 0) $newBalance = min($newBalance, 30); // CGU §6.5.3 : plafond 30 pts
        $this->update(['points_balance' => $newBalance]);
        $this->recalculateBadge();

        \App\Models\PointsHistory::create([
            'user_id'      => $this->id,
            'delta'        => $delta,
            'reason'       => $reason ?: ($delta >= 0 ? 'credit' : 'debit'),
            'balance_after' => $newBalance,
        ]);
    }

    public function recalculateBadge(): void
    {
        $balance      = $this->points_balance ?? 0;
        $platiniumMin = \App\Models\SystemSetting::get('badge_platinium_min', 20);
        $orMin        = \App\Models\SystemSetting::get('badge_or_min', 15);
        $argentMin    = \App\Models\SystemSetting::get('badge_argent_min', 10);
        $bronzeMin    = \App\Models\SystemSetting::get('badge_bronze_min', 5);

        $level = match (true) {
            $balance >= $platiniumMin => 'platinium',
            $balance >= $orMin        => 'or',
            $balance >= $argentMin    => 'argent',
            $balance >= $bronzeMin    => 'bronze',
            default                   => 'neutre',
        };

        if ($this->badge_level !== $level) {
            $this->update(['badge_level' => $level]);
        }
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

    public function eventPayments()
    {
        return $this->hasMany(EventPayment::class);
    }

    /** License held by this user (enterprise account holder). */
    public function enterpriseLicense()
    {
        return $this->hasOne(EnterpriseLicense::class, 'holder_user_id');
    }

    /** Invitation this user accepted as a member of someone else's enterprise pack. */
    public function enterpriseInvitation()
    {
        return $this->hasOne(EnterpriseInvitation::class, 'user_id')->where('status', 'active');
    }

    public function isEnterpriseHolder(): bool
    {
        return $this->enterpriseLicense()->exists();
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
        $this->loadMissing(['profile', 'company']);
        $profile = $this->profile;

        return !empty($this->first_name)
            && !empty($this->last_name)
            && !empty($this->phone)
            && !is_null($this->city_id)
            && !is_null($this->company_id)
            && !empty($profile?->avatar)
            && !empty($profile?->bio)
            && !empty($profile?->job_title)
            && !empty($profile?->sector_ids)
            && !empty($profile?->services_offered)
            && !empty($profile?->looking_for);
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
     * Get a specific feature from the user's plan.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getFeature(string $key, $default = null)
    {
        $plan = $this->subscription?->plan;

        if (! $plan) {
            // Fallback: look up the basic plan directly (no eager-load needed)
            $plan = \App\Models\Plan::where('name', 'basic')->first();
        }

        if (! $plan) {
            return $default;
        }

        $features = is_array($plan->features) ? $plan->features : [];

        return $features[$key] ?? $default;
    }

    public function canFeature(string $key): bool
    {
        // Resolve effective plan: ambassador > consul > subscription plan > basic
        $effectivePlanName = null;
        if ($this->isAmbassador())     $effectivePlanName = 'ambassadeur';
        elseif ($this->isConsul())     $effectivePlanName = 'consul';

        $plan = $effectivePlanName
            ? \App\Models\Plan::where('name', $effectivePlanName)->first()
            : ($this->subscription?->plan ?? \App\Models\Plan::where('name', 'basic')->first());

        if ($plan && is_array($plan->permissions) && array_key_exists($key, $plan->permissions)) {
            $val = $plan->permissions[$key];
            if ($val === null)      return true;
            if (is_bool($val))      return $val;
            if (is_int($val))       return $val > 0;
            return (bool) $val;
        }

        // Fallback to old features array
        $value = $this->getFeature($key);
        if ($value === null)    return true;
        if (is_bool($value))   return $value;
        if (is_int($value))    return $value > 0;
        return (bool) $value;
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

    /** Utilise notre PasswordResetMail personnalisé au lieu de la notification native. */
    public function sendPasswordResetNotification($token): void
    {
        $url = url(route('password.reset', ['token' => $token, 'email' => $this->email], false));

        Mail::to($this->email, $this->first_name . ' ' . $this->last_name)
            ->send(new PasswordResetMail($this, $url, config('auth.passwords.users.expire', 60)));
    }

    /** Utilise notre VerificationMail personnalisé au lieu de la notification native. */
    public function sendEmailVerificationNotification(): void
    {
        $url = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(config('auth.verification.expire', 60)),
            ['id' => $this->getKey(), 'hash' => sha1($this->getEmailForVerification())]
        );

        Mail::to($this->email, $this->first_name . ' ' . $this->last_name)
            ->send(new VerificationMail($this, $url));
    }
}
