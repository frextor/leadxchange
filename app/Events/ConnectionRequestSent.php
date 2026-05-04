<?php

namespace App\Events;

use App\Models\Connection;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConnectionRequestSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Connection $connection,
        public readonly User $sender
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.' . $this->connection->receiver_id)];
    }

    public function broadcastAs(): string
    {
        return 'ConnectionRequestSent';
    }

    public function broadcastWith(): array
    {
        return [
            'connection_id' => $this->connection->id,
            'sender'        => [
                'id'         => $this->sender->id,
                'first_name' => $this->sender->first_name,
                'last_name'  => $this->sender->last_name,
                'email'      => $this->sender->email,
            ],
            'created_at'    => $this->connection->created_at->toISOString(),
        ];
    }
}
