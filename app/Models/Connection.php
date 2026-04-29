<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Connection extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    /**
     * Get the user who sent the connection request.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the user who received the connection request.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Scope: Get pending connections.
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Get accepted connections.
     */
    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    /**
     * Scope: Get rejected connections.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Check if connection is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if connection is accepted.
     */
    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }

    /**
     * Check if connection is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }
}
