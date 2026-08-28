<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EnterpriseQuoteRejected extends Notification
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
            'type'    => 'enterprise_quote_rejected',
            'message' => "Votre demande de pack Entreprise pour « {$this->companyName} » n'a pas pu être retenue. N'hésitez pas à nous contacter pour plus d'informations.",
            'url'     => route('dashboard'),
        ];
    }
}
