<?php

namespace App\Notifications;

use App\Models\EnterpriseQuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EnterpriseProposalReceived extends Notification
{
    use Queueable;

    public function __construct(public readonly EnterpriseQuoteRequest $quote) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'enterprise_proposal_received',
            'message' => "Vous avez reçu une proposition de pack Entreprise pour « {$this->quote->company_name} ». Consultez-la et acceptez-la en ligne.",
            'url'     => route('enterprise.proposal.view', $this->quote->proposal_token),
        ];
    }
}
