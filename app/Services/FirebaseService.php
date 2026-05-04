<?php

namespace App\Services;

use App\Models\Connection;
use App\Models\DeviceToken;
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

        foreach ($tokens as $token) {
            $response = Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                    'message' => [
                        'token'        => $token,
                        'notification' => [
                            'title' => 'Nouvelle demande de connexion',
                            'body'  => "{$sender->first_name} {$sender->last_name} vous a envoyé une demande de connexion",
                        ],
                        'data' => [
                            'connection_id'      => (string) $connection->id,
                            'sender_id'          => (string) $sender->id,
                            'sender_first_name'  => $sender->first_name,
                            'sender_last_name'   => $sender->last_name,
                            'sender_email'       => $sender->email,
                            'type'               => 'connection_request',
                        ],
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
