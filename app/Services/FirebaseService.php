<?php

namespace App\Services;

use App\Models\Connection;
use App\Models\Conversation;
use App\Models\DeviceToken;
use App\Models\Group;
use App\Models\Message;
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
        $receiver = User::find($connection->receiver_id);
        if (!$receiver) {
            return;
        }

        $title = 'Nouvelle demande de connexion';
        $body  = "{$sender->first_name} {$sender->last_name} vous a envoyé une demande de connexion";

        // Always store DB notification regardless of push tokens
        $notificationId = (string) Notification::storeForUser($receiver, 'connection_request', $title, $body, [
            'user_id' => (string) $sender->id,
        ])->id;

        $tokens = DeviceToken::where('user_id', $connection->receiver_id)->pluck('token');
        if ($tokens->isEmpty()) {
            return;
        }

        $accessToken = $this->getAccessToken();
        $projectId   = config('firebase.project_id');

        $data = [
            'connection_id'     => (string) $connection->id,
            'sender_id'         => (string) $sender->id,
            'sender_first_name' => $sender->first_name,
            'sender_last_name'  => $sender->last_name,
            'sender_email'      => $sender->email,
            'type'              => 'connection_request',
        ];

        foreach ($tokens as $token) {
            $response = Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                    'message' => [
                        'token'   => $token,
                        'data'    => array_merge($data, ['title' => $title, 'body' => $body, 'notification_id' => $notificationId]),
                        'android' => ['priority' => 'high'],
                        'apns'    => ['payload' => ['aps' => ['alert' => ['title' => $title, 'body' => $body], 'sound' => 'default']]],
                    ],
                ]);

            if (!$response->successful()) {
                $errorCode = $response->json('error.details.0.errorCode') ?? '';
                Log::warning('FCM send failed', ['error' => $response->json('error.message')]);
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
        if ($tokens->isEmpty()) {
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

        $notificationId = null;
        $targetUser = User::find($targetUserId);
        if ($targetUser) {
            $notificationId = (string) Notification::storeForUser($targetUser, 'lead', $title, $body, [
                'lead_id' => (string) $lead->id,
            ])->id;
        }

        $accessToken = $this->getAccessToken();
        $projectId   = config('firebase.project_id');

        foreach ($tokens as $token) {
            $response = Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                    'message' => [
                        'token'   => $token,
                        'data'    => [
                            'title'            => $title,
                            'body'             => $body,
                            'notification_id'  => $notificationId ?? '',
                            'lead_id'          => (string) $lead->id,
                            'lead_title'       => $lead->title ?? '',
                            'actor_id'         => (string) $actor->id,
                            'actor_first_name' => $actor->first_name,
                            'actor_last_name'  => $actor->last_name,
                            'event'            => $event,
                            'type'             => 'lead',
                        ],
                        'android' => ['priority' => 'high'],
                        'apns'    => ['payload' => ['aps' => ['alert' => ['title' => $title, 'body' => $body], 'sound' => 'default']]],
                    ],
                ]);

            if (!$response->successful()) {
                $errorCode = $response->json('error.details.0.errorCode') ?? '';
                Log::warning('FCM lead notification failed', ['error' => $response->json('error.message')]);
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

        $notificationId = null;
        $receiver = User::find($lead->receiver_id);
        if ($receiver) {
            $notificationId = (string) Notification::storeForUser($receiver, 'lead_reminder', $title, $body, [
                'lead_id' => (string) $lead->id,
            ])->id;
        }

        if ($tokens->isEmpty()) {
            return;
        }

        $this->sendToTokens($tokens, $title, $body, [
            'lead_id'         => (string) $lead->id,
            'type'            => 'lead_reminder',
            'day'             => (string) $dayNumber,
            'notification_id' => $notificationId ?? '',
        ]);
    }

    public function sendGroupInviteNotification(User $invitee, \App\Models\Group $group, User $inviter): void
    {
        $title = 'Invitation à un groupe';
        $body  = "{$inviter->first_name} {$inviter->last_name} vous a invité à rejoindre le groupe : {$group->name}";

        $notificationId = (string) Notification::storeForUser($invitee, 'group_invite', $title, $body, [
            'group_id' => (string) $group->id,
        ])->id;

        $tokens = DeviceToken::where('user_id', $invitee->id)->pluck('token');
        if ($tokens->isEmpty()) {
            return;
        }

        $this->sendToTokens($tokens, $title, $body, [
            'type'            => 'group_invite',
            'group_id'        => (string) $group->id,
            'notification_id' => $notificationId,
        ]);
    }

    public function sendGroupAdminAssignedNotification(User $member, Group $group, User $actor): void
    {
        $title = 'Vous êtes maintenant administrateur';
        $body  = "{$actor->first_name} {$actor->last_name} vous a nommé administrateur du groupe {$group->name}.";

        $notificationId = (string) Notification::storeForUser($member, 'group_admin_assigned', $title, $body, [
            'group_id' => (string) $group->id,
        ])->id;

        $tokens = DeviceToken::where('user_id', $member->id)->pluck('token');
        if ($tokens->isEmpty()) {
            return;
        }

        $this->sendToTokens($tokens, $title, $body, [
            'type'            => 'group_admin_assigned',
            'group_id'        => (string) $group->id,
            'notification_id' => $notificationId,
        ]);
    }

    public function sendEventInviteNotification(User $invitee, \App\Models\Event $event, User $inviter): void
    {
        $title = 'Invitation à un événement';
        $body  = "{$inviter->first_name} {$inviter->last_name} vous a invité à : {$event->title}";

        $notificationId = (string) Notification::storeForUser($invitee, 'event_invite', $title, $body, [
            'event_id' => (string) $event->id,
        ])->id;

        $tokens = DeviceToken::where('user_id', $invitee->id)->pluck('token');
        if ($tokens->isEmpty()) {
            return;
        }

        $this->sendToTokens($tokens, $title, $body, [
            'type'            => 'event_invite',
            'event_id'        => (string) $event->id,
            'notification_id' => $notificationId,
        ]);
    }

    public function sendNewRegionalEventNotification(User $recipient, \App\Models\Event $event): void
    {
        $title = 'Nouvel événement dans votre région';
        $body  = $event->title . ($event->city ? ' — ' . $event->city->name : '');

        Notification::storeForUser($recipient, 'new_regional_event', $title, $body, [
            'event_id' => (string) $event->id,
        ]);

        $tokens = DeviceToken::where('user_id', $recipient->id)->pluck('token');
        if ($tokens->isEmpty()) {
            return;
        }

        $this->sendToTokens($tokens, $title, $body, [
            'type'     => 'new_regional_event',
            'event_id' => (string) $event->id,
        ]);
    }

    public function sendBadNoteWarning(User $sender, int $badNoteCount): void
    {
        $title = 'Avertissement qualité lead';
        $body  = "Vous avez reçu {$badNoteCount} évaluations négatives. Améliorez la qualité de vos leads pour éviter des pénalités.";

        $notificationId = (string) Notification::storeForUser($sender, 'bad_note_warning', $title, $body)->id;

        $tokens = DeviceToken::where('user_id', $sender->id)->pluck('token');
        if ($tokens->isEmpty()) {
            return;
        }

        $this->sendToTokens($tokens, $title, $body, [
            'type'            => 'bad_note_warning',
            'bad_note_count'  => (string) $badNoteCount,
            'notification_id' => $notificationId,
        ]);
    }

    // ─── Chat RTDB & Notification Methods ────────────────────────────────────

    /**
     * Write the message to Firebase RTDB and sync inbox for both participants.
     */
    public function syncChatMessage(Message $message, Conversation $conversation): void
    {
        $conversation->load(['user1.profile', 'user2.profile']);

        $receiverId = $message->receiver_id ?? $conversation->otherUser($message->sender_id)->id;

        $payload = [
            'id'                => (string) $message->id,
            'client_message_id' => $message->client_message_id,
            'conversation_id'   => (string) $message->conversation_id,
            'sender_id'         => (string) $message->sender_id,
            'receiver_id'       => (string) $receiverId,
            'type'              => $message->type,
            'body'              => $message->body,
            'media_url'         => $message->media_url,
            'caption'           => $message->caption,
            'filename'          => $message->filename,
            'duration_ms'       => $message->duration_ms,
            'created_at'        => $message->created_at->toIso8601String(),
            'read_at'           => null,
        ];

        $this->writeToDatabasePath("chat/{$message->conversation_id}/{$message->id}", $payload);

        $this->syncChatInboxForUser($conversation, $message->sender_id, $message);
        $this->syncChatInboxForUser($conversation, $receiverId, $message);
    }

    /**
     * Reset the unread count to 0 in the Firebase inbox for a user.
     */
    public function markChatRead(Conversation $conversation, int $userId): void
    {
        $this->writeToDatabasePath("chat_inbox/{$userId}/{$conversation->id}/unread_count", 0);
    }

    /**
     * Set or clear the typing indicator for a user in a conversation.
     */
    public function setChatTyping(Conversation $conversation, int $userId, bool $isTyping): void
    {
        $value = $isTyping ? now()->timestamp : null;
        $this->writeToDatabasePath("chat_typing/{$conversation->id}/{$userId}", $value);
    }

    /**
     * Send an FCM push notification for a new chat message.
     */
    public function sendChatNotification(Message $message, User $sender, User $receiver): void
    {
        $tokens = DeviceToken::where('user_id', $receiver->id)->pluck('token');
        if ($tokens->isEmpty()) {
            return;
        }

        $preview = match ($message->type) {
            'image' => $message->caption ? "📷 {$message->caption}" : '📷 Image',
            'audio' => '🎵 Audio',
            default => $message->body ?? '',
        };

        $title = $sender->full_name ?? $sender->email;

        $this->sendToTokens($tokens, $title, $preview, [
            'type'            => 'chat',
            'conversation_id' => (string) $message->conversation_id,
            'sender_id'       => (string) $sender->id,
        ]);
    }

    /**
     * Create a Firebase Auth custom token for the given Laravel user ID.
     * The mobile app passes this to signInWithCustomToken() to authenticate with Firebase.
     * The token UID equals the Laravel user ID as a string, satisfying Firebase rules
     * that check auth.uid == $userId.
     */
    public function createChatCustomToken(int $userId): string
    {
        $sa  = $this->loadServiceAccount();
        $now = time();

        $header  = $this->b64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $this->b64url(json_encode([
            'iss'    => $sa['client_email'],
            'sub'    => $sa['client_email'],
            'aud'    => 'https://identitytoolkit.googleapis.com/google.identity.identitytoolkit.v1.IdentityToolkit',
            'iat'    => $now,
            'exp'    => $now + 3600,
            'uid'    => (string) $userId,
            'claims' => new \stdClass(),
        ]));

        $signingInput = $header . '.' . $payload;
        $key          = openssl_pkey_get_private($sa['private_key']);
        openssl_sign($signingInput, $signature, $key, OPENSSL_ALGO_SHA256);

        return $signingInput . '.' . $this->b64url($signature);
    }

    private function syncChatInboxForUser(Conversation $conversation, int $userId, Message $message): void
    {
        $otherUser = $conversation->otherUser($userId);

        $preview = match ($message->type) {
            'image' => $message->caption ?: '📷 Image',
            'audio' => '🎵 Audio',
            default => $message->body ?? '',
        };

        $unreadCount = $conversation->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('read_at')
            ->count();

        $payload = [
            'conversation_id'       => (string) $conversation->id,
            'other_user_id'         => (string) $otherUser->id,
            'other_user_name'       => $otherUser->full_name ?? '',
            'other_user_avatar'     => $otherUser->profile?->avatar_url,
            'last_message_preview'  => $preview,
            'last_message_type'     => $message->type,
            'last_message_at'       => $message->created_at->toIso8601String(),
            'last_message_is_mine'  => $message->sender_id === $userId,
            'unread_count'          => $unreadCount,
        ];

        $this->writeToDatabasePath("chat_inbox/{$userId}/{$conversation->id}", $payload);
    }

    /**
     * Write data to a Firebase Realtime Database path via REST API.
     * Passing null deletes the path.
     */
    private function writeToDatabasePath(string $path, mixed $data): void
    {
        $databaseUrl = rtrim((string) config('firebase.database_url'), '/');
        $url         = "{$databaseUrl}/{$path}.json";

        if ($data === null) {
            Http::delete($url);
        } else {
            Http::withBody(json_encode($data), 'application/json')->put($url);
        }
    }

    /**
     * Get an OAuth access token scoped for Firebase Realtime Database writes.
     * Cached separately from the FCM token (different scope).
     */
    private function getDatabaseAccessToken(): string
    {
        return Cache::remember('firebase_database_access_token', 3500, function () {
            $sa  = $this->loadServiceAccount();
            $now = time();

            $header  = $this->b64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $payload = $this->b64url(json_encode([
                'iss'   => $sa['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
            ]));

            $signingInput = $header . '.' . $payload;
            $key          = openssl_pkey_get_private($sa['private_key']);
            openssl_sign($signingInput, $signature, $key, OPENSSL_ALGO_SHA256);

            $jwt = $signingInput . '.' . $this->b64url($signature);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to get Firebase Database access token: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    // ─── Internal Helpers ─────────────────────────────────────────────────────

    public function sendAmbassadorAnnouncement(\Illuminate\Support\Collection $members, string $title, string $body): void
    {
        $userIds = $members->pluck('id');
        $tokens  = DeviceToken::whereIn('user_id', $userIds)->pluck('token');

        if ($tokens->isEmpty()) {
            return;
        }

        $this->sendToTokens($tokens, $title, $body, [
            'type' => 'ambassador_announcement',
        ]);
    }

    public function sendConsulRequestNotification(User $requester): void
    {
        if (!$requester->city_id) {
            return;
        }

        $ambassadors = User::where('ambassador_status', 'approved')
            ->where('city_id', $requester->city_id)
            ->where('id', '!=', $requester->id)
            ->get();

        if ($ambassadors->isEmpty()) {
            return;
        }

        $title = 'Nouvelle demande Consul';
        $body  = "{$requester->first_name} {$requester->last_name} demande le statut Consul dans votre région.";

        foreach ($ambassadors as $ambassador) {
            $notificationId = (string) Notification::storeForUser($ambassador, 'consul_request_submitted', $title, $body, [
                'user_id' => (string) $requester->id,
            ])->id;

            $tokens = DeviceToken::where('user_id', $ambassador->id)->pluck('token');
            if ($tokens->isEmpty()) {
                continue;
            }

            $this->sendToTokens($tokens, $title, $body, [
                'type'            => 'connection_request',
                'target_id'       => (string) $requester->id,
                'notification_id' => $notificationId,
            ]);
        }
    }

    private function sendToTokens($tokens, string $title, string $body, array $data = []): void
    {
        $accessToken = $this->getAccessToken();
        $projectId   = config('firebase.project_id');

        foreach ($tokens as $token) {
            $response = Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                    'message' => [
                        'token'   => $token,
                        'data'    => array_merge($data, ['title' => $title, 'body' => $body]),
                        'android' => ['priority' => 'high'],
                        'apns'    => ['payload' => ['aps' => ['alert' => ['title' => $title, 'body' => $body], 'sound' => 'default']]],
                    ],
                ]);

            if (!$response->successful()) {
                $errorCode = $response->json('error.details.0.errorCode') ?? '';
                Log::warning('FCM notification failed', ['error' => $response->json('error.message')]);
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
