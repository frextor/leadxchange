<?php

namespace App\Services;

use App\Models\Connection;
use App\Models\DeviceToken;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebaseService
{
    private ?array $serviceAccount = null;

    public function sendConnectionNotification(Connection $connection, User $sender): void
    {
        $tokens = DeviceToken::where('user_id', $connection->receiver_id)->pluck('token');

        if ($tokens->isEmpty()) {
            return;
        }

        $accessToken = $this->getAccessToken();
        $projectId   = config('firebase.project_id');

        $title = 'Nouvelle demande de connexion';
        $body  = "{$sender->first_name} {$sender->last_name} vous a envoyé une demande de connexion";
        $data  = [
            'connection_id'     => (string) $connection->id,
            'sender_id'         => (string) $sender->id,
            'sender_first_name' => $sender->first_name,
            'sender_last_name'  => $sender->last_name,
            'sender_email'      => $sender->email,
            'type'              => 'connection_request',
        ];

        $receiver = User::find($connection->receiver_id);
        if ($receiver) {
            Notification::storeForUser($receiver, 'connection_request', $title, $body, [
                'user_id' => (string) $sender->id,
            ]);
        }

        foreach ($tokens as $token) {
            $response = Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                    'message' => [
                        'token'        => $token,
                        'notification' => ['title' => $title, 'body' => $body],
                        'data'         => $data,
                    ],
                ]);

            if (!$response->successful()) {
                $errorCode = $response->json('error.details.0.errorCode') ?? '';
                Log::warning('FCM send failed', [
                    'token_prefix' => substr($token, 0, 20),
                    'error'        => $response->json('error.message'),
                ]);
                if (in_array($errorCode, ['UNREGISTERED', 'INVALID_ARGUMENT'])) {
                    DeviceToken::where('token', $token)->delete();
                }
            }
        }
    }

    public function sendLeadNotification(\App\Models\Lead $lead, User $actor, string $event): void
    {
        // 'sent'     → notify the receiver
        // 'accepted' → notify the sender
        // 'rejected' → notify the sender
        $targetUserId = match ($event) {
            'sent'              => $lead->receiver_id,
            'accepted', 'rejected' => $lead->sender_id,
            default             => null,
        };

        if (!$targetUserId) {
            return;
        }

        $tokens = DeviceToken::where('user_id', $targetUserId)->pluck('token');
        Log::info('FCM sendLeadNotification tokens', ['target_user_id' => $targetUserId, 'token_count' => count($tokens)]);
        if ($tokens->isEmpty()) {
            Log::warning('FCM sendLeadNotification: no tokens for user', ['target_user_id' => $targetUserId]);
            return;
        }

        $leadLabel = $lead->company_name ?? 'Lead';

        [$title, $body] = match ($event) {
            'sent'     => [
                'Nouveau lead reçu',
                "{$actor->first_name} {$actor->last_name} vous a envoyé un lead : {$leadLabel}",
            ],
            'accepted' => [
                'Lead accepté',
                "{$actor->first_name} {$actor->last_name} a accepté votre lead : {$leadLabel}",
            ],
            'rejected' => [
                'Lead refusé',
                "{$actor->first_name} {$actor->last_name} a refusé votre lead : {$leadLabel}",
            ],
            default    => ['Lead update', $leadLabel],
        };

        $targetUser = User::find($targetUserId);
        if ($targetUser) {
            Notification::storeForUser($targetUser, 'lead', $title, $body, [
                'lead_id' => (string) $lead->id,
            ]);
        }

        $accessToken = $this->getAccessToken();
        $projectId   = config('firebase.project_id');

        Log::info('FCM sendLeadNotification', [
            'project_id'    => $projectId,
            'token_count'   => count($tokens),
            'lead_id'       => $lead->id,
            'target_user_id'=> $targetUserId,
            'event'         => $event,
            'title'         => $title,
        ]);

        foreach ($tokens as $token) {
            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
            $response = Http::withToken($accessToken)
                ->post($url, [
                    'message' => [
                        'token'        => $token,
                        'notification' => ['title' => $title, 'body' => $body],
                        'data'         => [
                            'lead_id'          => (string) $lead->id,
                            'lead_title'       => $lead->title ?? '',
                            'actor_id'         => (string) $actor->id,
                            'actor_first_name' => $actor->first_name,
                            'actor_last_name'  => $actor->last_name,
                            'event'            => $event,
                            'type'             => 'lead',
                        ],
                    ],
                ]);

            if ($response->successful()) {
                Log::info('FCM lead send OK', ['token_prefix' => substr($token, 0, 20), 'message_id' => $response->json('name')]);
            } else {
                $errorCode = $response->json('error.details.0.errorCode') ?? '';
                Log::error('FCM lead send FAILED', [
                    'token_prefix'  => substr($token, 0, 20),
                    'status'        => $response->status(),
                    'error'         => $response->json('error.message'),
                    'error_code'    => $errorCode,
                    'full_response' => $response->body(),
                ]);
                if (in_array($errorCode, ['UNREGISTERED', 'INVALID_ARGUMENT'])) {
                    DeviceToken::where('token', $token)->delete();
                }
            }
        }
    }

    public function sendLeadReminderNotification(\App\Models\Lead $lead, int $dayNumber): void
    {
        $tokens = DeviceToken::where('user_id', $lead->receiver_id)->pluck('token');

        $title = 'Rappel : lead en attente de notation';
        $body  = "Vous avez {$dayNumber} jours pour noter le lead de {$lead->company_name}. Votre avis compte !";

        $receiver = User::find($lead->receiver_id);
        if ($receiver) {
            Notification::storeForUser($receiver, 'lead_reminder', $title, $body, [
                'lead_id' => (string) $lead->id,
            ]);
        }

        if ($tokens->isEmpty()) {
            return;
        }

        $this->sendToTokens($tokens, $title, $body, [
            'lead_id' => (string) $lead->id,
            'type'    => 'lead_reminder',
            'day'     => (string) $dayNumber,
        ]);
    }

    public function sendGroupInviteNotification(User $invitee, \App\Models\Group $group, User $inviter): void
    {
        $title = 'Invitation à un groupe';
        $body  = "{$inviter->first_name} {$inviter->last_name} vous a invité à rejoindre le groupe : {$group->name}";

        Notification::storeForUser($invitee, 'group_invite', $title, $body, [
            'group_id' => (string) $group->id,
        ]);

        $tokens = DeviceToken::where('user_id', $invitee->id)->pluck('token');
        if ($tokens->isEmpty()) {
            return;
        }

        $this->sendToTokens($tokens, $title, $body, [
            'type'     => 'group_invite',
            'group_id' => (string) $group->id,
        ]);
    }

    public function sendEventInviteNotification(User $invitee, \App\Models\Event $event, User $inviter): void
    {
        $title = 'Invitation à un événement';
        $body  = "{$inviter->first_name} {$inviter->last_name} vous a invité à : {$event->title}";

        Notification::storeForUser($invitee, 'event_invite', $title, $body, [
            'event_id' => (string) $event->id,
        ]);

        $tokens = DeviceToken::where('user_id', $invitee->id)->pluck('token');
        if ($tokens->isEmpty()) {
            return;
        }

        $this->sendToTokens($tokens, $title, $body, [
            'type'     => 'event_invite',
            'event_id' => (string) $event->id,
        ]);
    }

    public function sendBadNoteWarning(User $sender, int $badNoteCount): void
    {
        $title = 'Avertissement qualité lead';
        $body  = "Vous avez reçu {$badNoteCount} évaluations négatives. Améliorez la qualité de vos leads pour éviter des pénalités.";

        Notification::storeForUser($sender, 'bad_note_warning', $title, $body);

        $tokens = DeviceToken::where('user_id', $sender->id)->pluck('token');
        if ($tokens->isEmpty()) {
            return;
        }

        $this->sendToTokens($tokens, $title, $body, [
            'type'           => 'bad_note_warning',
            'bad_note_count' => (string) $badNoteCount,
        ]);
    }

    private function sendToTokens($tokens, string $title, string $body, array $data = []): void
    {
        $accessToken = $this->getAccessToken();
        $projectId   = config('firebase.project_id');

        Log::info('FCM sendToTokens', [
            'project_id'   => $projectId,
            'token_count'  => count($tokens),
            'title'        => $title,
            'data_keys'    => array_keys($data),
        ]);

        foreach ($tokens as $token) {
            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";
            $response = Http::withToken($accessToken)
                ->post($url, [
                    'message' => [
                        'token'        => $token,
                        'notification' => ['title' => $title, 'body' => $body],
                        'data'         => $data,
                    ],
                ]);

            if ($response->successful()) {
                Log::info('FCM send OK', ['token_prefix' => substr($token, 0, 20), 'message_id' => $response->json('name')]);
            } else {
                $errorCode = $response->json('error.details.0.errorCode') ?? '';
                Log::error('FCM send FAILED', [
                    'token_prefix' => substr($token, 0, 20),
                    'status'       => $response->status(),
                    'error'        => $response->json('error.message'),
                    'error_code'   => $errorCode,
                    'full_response'=> $response->body(),
                ]);
                if (in_array($errorCode, ['UNREGISTERED', 'INVALID_ARGUMENT'])) {
                    DeviceToken::where('token', $token)->delete();
                }
            }
        }
    }

    private function getAccessToken(): string
    {
        return Cache::remember('firebase_fcm_access_token', 3500, function () {
            $sa  = $this->loadServiceAccount();
            $jwt = $this->createJwt($sa['client_email'], $sa['private_key']);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to get FCM access token: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    private function loadServiceAccount(): array
    {
        if ($this->serviceAccount !== null) {
            return $this->serviceAccount;
        }

        $path = config('firebase.service_account_json');

        if (!$path || !file_exists($path)) {
            throw new \Exception('Firebase service account JSON not found. Set FIREBASE_SERVICE_ACCOUNT_JSON in .env');
        }

        $this->serviceAccount = json_decode(file_get_contents($path), true);
        return $this->serviceAccount;
    }

    private function createJwt(string $clientEmail, string $privateKey): string
    {
        $now = time();

        $header  = $this->b64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $this->b64url(json_encode([
            'iss'   => $clientEmail,
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
        ]));

        $signingInput = $header . '.' . $payload;
        $key          = openssl_pkey_get_private($privateKey);
        openssl_sign($signingInput, $signature, $key, OPENSSL_ALGO_SHA256);

        return $signingInput . '.' . $this->b64url($signature);
    }

    private function b64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
