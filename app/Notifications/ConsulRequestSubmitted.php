<?php
namespace App\Notifications;
use App\Models\ConsulRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
class ConsulRequestSubmitted extends Notification
{
    use Queueable;
    public function __construct(public readonly ConsulRequest $consulRequest) {}
    public function via(object $notifiable): array { return ['database']; }
    public function toArray(object $notifiable): array {
        $user = $this->consulRequest->user;
        return [
            'type'       => 'consul_request_submitted',
            'message'    => "{$user->first_name} {$user->last_name} demande le rôle Consul.",
            'url'        => route('admin.consul.index'),
            'user_id'    => $user->id,
            'request_id' => $this->consulRequest->id,
        ];
    }
}
