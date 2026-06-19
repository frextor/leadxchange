<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
class ConsulRequestRejected extends Notification
{
    use Queueable;
    public function __construct(public readonly ?string $reason = null) {}
    public function via(object $notifiable): array { return ['database']; }
    public function toArray(object $notifiable): array {
        return [
            'type'    => 'consul_request_rejected',
            'message' => 'Votre demande de rôle Consul a été refusée.' . ($this->reason ? ' Raison : ' . $this->reason : ''),
            'url'     => route('dashboard'),
        ];
    }
}
