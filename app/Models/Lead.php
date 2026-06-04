<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Lead extends Model
{
    // ── Status constants ────────────────────────────────────────────────────
    const STATUS_NEW       = 'new';
    const STATUS_ACCEPTED  = 'accepted';
    const STATUS_REJECTED  = 'rejected';
    const STATUS_CONVERTED = 'converted';
    const STATUS_EXPIRED   = 'expired';

    // ── Qualification constants ─────────────────────────────────────────────
    const QUAL_CHAUD = 'chaud';
    const QUAL_TIEDE = 'tiede';
    const QUAL_FROID = 'froid';

    public static array $qualificationConfig = [
        'chaud' => ['label' => 'Chaud', 'icon' => '🔥', 'classes' => 'bg-red-100 text-red-700',    'textClass' => 'text-red-700',   'barClass' => 'bg-red-500'],
        'tiede' => ['label' => 'Tiède', 'icon' => '⚡', 'classes' => 'bg-amber-100 text-amber-700', 'textClass' => 'text-amber-700', 'barClass' => 'bg-amber-400'],
        'froid' => ['label' => 'Froid', 'icon' => '❄️', 'classes' => 'bg-blue-100 text-blue-600',  'textClass' => 'text-blue-600',  'barClass' => 'bg-blue-400'],
    ];

    // ── Status labels & colors for UI ────────────────────────────────────────
    public static array $statusConfig = [
        'new'       => ['label' => 'En attente', 'classes' => 'bg-blue-50 text-blue-600',    'dot' => 'bg-blue-500'],
        'accepted'  => ['label' => 'Accepté',    'classes' => 'bg-emerald-50 text-emerald-600', 'dot' => 'bg-emerald-500'],
        'rejected'  => ['label' => 'Refusé',     'classes' => 'bg-red-50 text-red-500',      'dot' => 'bg-red-500'],
        'converted' => ['label' => 'Converti',   'classes' => 'bg-teal-50 text-teal-700',    'dot' => 'bg-teal-500'],
        'expired'   => ['label' => 'Expiré',     'classes' => 'bg-gray-50 text-gray-500',    'dot' => 'bg-gray-400'],
    ];

    protected $fillable = [
        'sender_id', 'receiver_id',
        'company_name', 'contact_name', 'contact_email',
        'contact_phone', 'contact_position',
        'deadline', 'qualification', 'sector_id', 'description', 'status',
        'points_deducted', 'rated_bonus_at',
        'fraud_reported', 'fraud_reason', 'fraud_reported_at',
        'reminder_15_sent_at', 'reminder_25_sent_at',
    ];

    protected $casts = [
        'deadline'             => 'date',
        'points_deducted'      => 'boolean',
        'rated_bonus_at'       => 'datetime',
        'fraud_reported'       => 'boolean',
        'fraud_reported_at'    => 'datetime',
        'reminder_15_sent_at'  => 'datetime',
        'reminder_25_sent_at'  => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(LeadRating::class);
    }

    public function sector(): BelongsTo
    {
        return $this->belongsTo(Sector::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function isFraudReported(): bool { return (bool) $this->fraud_reported; }

    public function isNew(): bool       { return $this->status === self::STATUS_NEW; }
    public function isAccepted(): bool  { return $this->status === self::STATUS_ACCEPTED; }
    public function isRejected(): bool  { return $this->status === self::STATUS_REJECTED; }
    public function isConverted(): bool { return $this->status === self::STATUS_CONVERTED; }
    public function isExpired(): bool   { return $this->status === self::STATUS_EXPIRED; }

    public function getQualificationConfigAttribute(): array
    {
        return self::$qualificationConfig[$this->qualification]
            ?? ['label' => $this->qualification, 'icon' => '•', 'classes' => 'bg-gray-100 text-gray-600', 'textClass' => 'text-gray-600'];
    }

    public function getStatusConfigAttribute(): array
    {
        return self::$statusConfig[$this->status]
            ?? ['label' => $this->status, 'classes' => 'bg-gray-100 text-gray-600', 'dot' => 'bg-gray-400'];
    }

    public function getAverageRatingAttribute(): ?float
    {
        if ($this->ratings->isEmpty()) {
            return null;
        }
        return round($this->ratings->avg('average_note'), 2);
    }

    public function hasRatingBy(int $userId): bool
    {
        return $this->ratings()->where('rater_id', $userId)->exists();
    }
}
