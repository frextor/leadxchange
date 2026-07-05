<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    protected $fillable = [
        'title', 'description', 'type', 'category', 'location', 'meeting_link',
        'starts_at', 'ends_at', 'created_by', 'sector_id', 'city_id', 'region_id',
        'cover_color', 'cover_image', 'price', 'max_attendees', 'attendees_count', 'is_public',
    ];

    public static array $categoryLabels = [
        'networking'  => 'Networking',
        'workshop'    => 'Workshop',
        'conference'  => 'Conference',
        'pitch'       => 'Pitch',
        'after_work'  => 'After-work',
        'webinar'     => 'Online / Webinar',
        'community'   => 'Community',
    ];

    public function getCoverUrlAttribute(): ?string
    {
        if (!$this->cover_image) return null;
        if (str_starts_with($this->cover_image, 'http')) return $this->cover_image;
        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->cover_image);
    }

    public function getIsFreeAttribute(): bool
    {
        return is_null($this->price) || $this->price == 0;
    }

    protected $casts = [
        'starts_at'  => 'datetime',
        'ends_at'    => 'datetime',
        'is_public'  => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function attendees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'event_user')
                    ->withPivot('role', 'registered_at');
    }

    public function payments()
    {
        return $this->hasMany(EventPayment::class);
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(\App\Models\City::class);
    }

    public function isAttending(int $userId): bool
    {
        return $this->attendees()->where('user_id', $userId)->exists();
    }
}
