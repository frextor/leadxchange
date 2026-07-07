<?php

namespace App\Observers;

use App\Models\Lead;
use App\Models\LeadRating;
use App\Models\User;
use App\Services\LeadScoreService;

class LeadRatingObserver
{
    public function __construct(private LeadScoreService $scorer) {}

    public function created(LeadRating $rating): void
    {
        $lead = Lead::find($rating->lead_id);
        if (!$lead) return;

        if ($lead->sender_id)   $this->scorer->updateUser(User::find($lead->sender_id));
        if ($lead->receiver_id) $this->scorer->updateUser(User::find($lead->receiver_id));
    }
}
