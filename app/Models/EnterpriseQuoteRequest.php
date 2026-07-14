<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnterpriseQuoteRequest extends Model
{
    protected $fillable = [
        'user_id', 'company_name', 'seats_needed',
        'phone', 'message', 'status', 'admin_notes',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static array $statusLabels = [
        'pending'   => ['label' => 'En attente',  'bg' => '#FEF3C7', 'color' => '#92400E'],
        'contacted' => ['label' => 'Contacté',    'bg' => '#DBEAFE', 'color' => '#1E40AF'],
        'converted' => ['label' => 'Converti',    'bg' => '#D1FAE5', 'color' => '#065F46'],
        'closed'    => ['label' => 'Fermé',       'bg' => '#F3F4F6', 'color' => '#6B7280'],
    ];
}
