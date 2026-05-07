<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Profile extends Model
{
    protected $fillable = [
        'user_id', 'avatar', 'bio', 'motto', 'job_title', 'sector',
        'experience_level', 'looking_for', 'services_offered', 'open_to_network',
        'website', 'region', 'linkedin', 'sector_ids',
    ];

    protected $casts = [
        'open_to_network'  => 'boolean',
        'looking_for'      => 'array',
        'services_offered' => 'array',
        'sector_ids'       => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? Storage::url($this->avatar) : null;
    }
}
