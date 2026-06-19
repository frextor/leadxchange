<?php
namespace App\Notifications;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
class ConsulRequestApproved extends Notification
{
    use Queueable;
    public function via(object $notifiable): array { return ['database']; }
    public function toArray(object $notifiable): array {
        return [
            'type'    => 'consul_request_approved',
            'message' => 'Félicitations ! Votre demande de rôle Consul a été approuvée.',
            'url'     => route('dashboard'),
        ];
    }
}
