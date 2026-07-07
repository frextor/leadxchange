<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AmbassadorObjective extends Model
{
    protected $fillable = [
        'ambassador_id', 'month', 'year',
        'target_members', 'target_events', 'target_leads',
    ];

    protected $casts = [
        'month' => 'integer',
        'year'  => 'integer',
        'target_members' => 'integer',
        'target_events'  => 'integer',
        'target_leads'   => 'integer',
    ];

    public function ambassador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ambassador_id');
    }
}
