<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EnterpriseQuoteRequest extends Model
{
    protected $fillable = [
        'user_id', 'company_name', 'seats_needed',
        'phone', 'message', 'status', 'admin_notes',
        'proposed_seats', 'plan_id', 'proposed_price',
        'proposed_duration_months', 'proposal_message',
        'stripe_payment_link', 'stripe_price_id',
        'proposal_token', 'proposal_sent_at', 'proposal_accepted_at',
    ];

    protected $casts = [
        'proposal_sent_at'     => 'datetime',
        'proposal_accepted_at' => 'datetime',
        'proposed_price'       => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function hasProposal(): bool
    {
        return $this->proposal_sent_at !== null;
    }

    public function isAccepted(): bool
    {
        return $this->proposal_accepted_at !== null;
    }

    public static function generateToken(): string
    {
        return Str::random(64);
    }

    public static array $statusLabels = [
        'pending'   => ['label' => 'En attente',      'bg' => '#FEF3C7', 'color' => '#92400E'],
        'contacted' => ['label' => 'Contacté',         'bg' => '#DBEAFE', 'color' => '#1E40AF'],
        'proposed'  => ['label' => 'Proposition envoyée', 'bg' => '#EDE9FE', 'color' => '#5B21B6'],
        'converted' => ['label' => 'Converti',         'bg' => '#D1FAE5', 'color' => '#065F46'],
        'closed'    => ['label' => 'Fermé',            'bg' => '#F3F4F6', 'color' => '#6B7280'],
    ];
}
