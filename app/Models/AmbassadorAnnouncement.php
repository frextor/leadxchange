<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AmbassadorAnnouncement extends Model
{
    protected $fillable = [
        'ambassador_id', 'region_id', 'subject', 'body',
        'type', 'recipients_count', 'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function ambassador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ambassador_id');
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(City::class, 'region_id');
    }
}
