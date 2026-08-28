<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EnterpriseQuoteAccepted extends Notification
{
    use Queueable;

    public function __construct(public readonly string $companyName) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'enterprise_quote_accepted',
            'message' => "Bonne nouvelle ! Votre demande de pack Entreprise pour « {$this->companyName} » a été acceptée. Notre équipe va vous contacter très prochainement.",
            'url'     => route('dashboard'),
        ];
    }
}
