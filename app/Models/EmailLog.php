<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class EmailLog extends Model
{
    protected $fillable = [
        'recipient_email', 'recipient_name', 'subject', 'type',
        'status', 'error_message', 'metadata', 'mailer', 'sent_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent_at'  => 'datetime',
    ];

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'sent'    => '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700">Envoyé</span>',
            'failed'  => '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-700">Échoué</span>',
            default   => '<span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-gray-100 text-gray-600">En attente</span>',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match ($this->type) {
            'verification'   => '✉️',
            'password_reset' => '🔑',
            'notification'   => '🔔',
            'marketing'      => '📢',
            'test'           => '🧪',
            default          => '📧',
        };
    }
}
