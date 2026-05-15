<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadRating extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'lead_id', 'rater_id', 'quality', 'relevance', 'reactivity',
    ];

    protected $casts = [
        'quality'      => 'integer',
        'relevance'    => 'integer',
        'reactivity'   => 'integer',
        'average_note' => 'decimal:2',
        'rated_at'     => 'datetime',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function rater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rater_id');
    }
}
