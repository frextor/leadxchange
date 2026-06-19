<?php

namespace App\Observers;

use App\Models\Lead;
use App\Models\User;
use App\Services\LeadScoreService;

class LeadObserver
{
    public function __construct(private LeadScoreService $scorer) {}

    public function created(Lead $lead): void
    {
        $this->recalculate($lead);
    }

    public function updated(Lead $lead): void
    {
        // Recalcule si le statut ou le type a changé
        if ($lead->wasChanged(['status', 'lead_type'])) {
            $this->recalculate($lead);
        }
    }

    public function deleted(Lead $lead): void
    {
        $this->recalculate($lead);
    }

    private function recalculate(Lead $lead): void
    {
        if ($lead->sender_id)   $this->scorer->updateUser(User::find($lead->sender_id));
        if ($lead->receiver_id) $this->scorer->updateUser(User::find($lead->receiver_id));
    }
}
