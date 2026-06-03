<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Group extends Model
{
    protected $fillable = [
        'name', 'description', 'sector_id', 'city_id', 'created_by',
        'cover_color', 'cover_photo', 'is_public', 'members_count',
    ];

    protected $casts = ['is_public' => 'boolean'];

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(\App\Models\City::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'group_user')
                    ->withPivot('role', 'joined_at');
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(GroupInvitation::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(GroupPost::class);
    }

    public function userRole(int $userId): ?string
    {
        return $this->members()->where('group_user.user_id', $userId)->value('group_user.role');
    }

    public function isMember(int $userId): bool
    {
        return $this->members()->where('group_user.user_id', $userId)->exists();
    }

    public function isOwner(int $userId): bool
    {
        return $this->created_by === $userId ||
            $this->members()->where('group_user.user_id', $userId)->where('group_user.role', 'owner')->exists();
    }

    public function polls(): HasMany
    {
        return $this->hasMany(Poll::class);
    }

    public function isAdmin(int $userId): bool
    {
        return $this->created_by === $userId ||
            $this->members()->where('group_user.user_id', $userId)->whereIn('group_user.role', ['owner', 'admin'])->exists();
    }
}
