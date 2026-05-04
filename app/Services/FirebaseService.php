<?php

namespace App\Services;

use App\Models\Connection;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class FirebaseService
{
    private string $databaseUrl;

    public function __construct()
    {
        $this->databaseUrl = rtrim(config('firebase.database.url'), '/');
    }

    public function sendConnectionNotification(Connection $connection, User $sender): void
    {
        Http::post(
            "{$this->databaseUrl}/notifications/{$connection->receiver_id}.json",
            [
                'connection_id' => $connection->id,
                'sender'        => [
                    'id'         => $sender->id,
                    'first_name' => $sender->first_name,
                    'last_name'  => $sender->last_name,
                    'email'      => $sender->email,
                ],
                'timestamp'     => (int) (microtime(true) * 1000),
                'read'          => false,
            ]
        );
    }
}
