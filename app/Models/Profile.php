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
        'website', 'linkedin', 'sector_ids', 'presentation_video',
        'presentation_video_status', 'presentation_video_rejection_reason',
        'presentation_video_uploaded_at', 'presentation_video_reviewed_at',
        'presentation_video_reviewed_by',
    ];

    protected $casts = [
        'open_to_network'  => 'boolean',
        'looking_for'      => 'array',
        'services_offered' => 'array',
        'sector_ids'       => 'array',
        'presentation_video_uploaded_at' => 'datetime',
        'presentation_video_reviewed_at' => 'datetime',
    ];

    protected $appends = ['avatar_url', 'presentation_video_url'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if (!$this->avatar) return null;
        if (str_starts_with($this->avatar, 'http')) return $this->avatar;
        return Storage::disk('public')->url($this->avatar);
    }

    public function getPresentationVideoUrlAttribute(): ?string
    {
        if (!$this->presentation_video) return null;
        if (str_starts_with($this->presentation_video, 'http')) return $this->presentation_video;
        return Storage::disk('public')->url($this->presentation_video);
    }
}
