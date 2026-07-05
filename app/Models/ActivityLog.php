<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'causer_id',
        'event',
        'description',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    /** Return the broad category for display/filtering. */
    public function category(): string
    {
        if (str_contains($this->event, 'login_failed')) {
            return 'security';
        }

        return match (true) {
            str_starts_with($this->event, 'auth.')    => 'auth',
            str_starts_with($this->event, 'admin.')   => 'admin',
            str_starts_with($this->event, 'payment.') => 'payment',
            default                                    => 'other',
        };
    }
}
