<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupPost extends Model
{
    protected $fillable = ['group_id', 'user_id', 'body', 'type', 'photo_path', 'activity_title', 'activity_date'];

    protected $casts = ['activity_date' => 'datetime'];

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path
            ? \Illuminate\Support\Facades\Storage::url($this->photo_path)
            : null;
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(GroupPostComment::class, 'post_id')->with('author.profile')->orderBy('created_at');
    }
}
