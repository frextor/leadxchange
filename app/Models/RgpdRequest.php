<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RgpdRequest extends Model
{
    protected $fillable = [
        'user_id', 'right_type', 'details', 'status',
        'admin_notes', 'processed_by', 'processed_at',
    ];

    protected $casts = ['processed_at' => 'datetime'];

    public const RIGHTS = [
        'access'       => 'Droit d\'accès',
        'rectification'=> 'Droit de rectification',
        'erasure'      => 'Droit à l\'effacement',
        'portability'  => 'Droit à la portabilité',
        'opposition'   => 'Droit d\'opposition',
        'limitation'   => 'Droit à la limitation',
    ];

    public const STATUSES = [
        'pending'    => ['label' => 'En attente',   'color' => 'amber'],
        'processing' => ['label' => 'En cours',     'color' => 'blue'],
        'completed'  => ['label' => 'Traité',       'color' => 'emerald'],
        'rejected'   => ['label' => 'Rejeté',       'color' => 'red'],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
