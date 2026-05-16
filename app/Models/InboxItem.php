<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InboxItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'kind', 'type', 'read', 'archived', 'ts',
        'actor_id', 'actor_name', 'actor_title', 'actor_company',
        'lead_ref', 'lead_id', 'title', 'preview', 'body', 'user_id',
    ];

    protected $casts = [
        'read'     => 'boolean',
        'archived' => 'boolean',
        'ts'       => 'datetime',
    ];

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getRefIdAttribute(): string
    {
        return 'I-' . $this->id;
    }
}
