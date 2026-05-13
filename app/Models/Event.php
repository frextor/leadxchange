<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Event extends Model
{
    protected $fillable = [
        'title', 'description', 'type', 'location', 'meeting_link',
        'starts_at', 'ends_at', 'created_by', 'sector_id',
        'cover_color', 'max_attendees', 'attendees_count', 'is_public',
    ];

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

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    public function isAttending(int $userId): bool
    {
        return $this->attendees()->where('user_id', $userId)->exists();
    }
}
