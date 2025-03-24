<?php
namespace App\Services;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AppleAuthService
{
    protected $keys;
    protected $clientId;
    protected $teamId;
    protected $keyId;
    protected $privateKey;

    public function __construct()
    {
        $this->clientId = config('services.apple.client_id');
        $this->teamId = config('services.apple.team_id');
        $this->keyId = config('services.apple.key_id');
        $this->privateKey = config('services.apple.private_key');
    }

    public function verifyToken($token)
    {
        try {
            // Fetch Apple's public keys if not already cached
            if (!$this->keys) {
                $response = Http::get('https://appleid.apple.com/auth/keys');
                $this->keys = JWK::parseKeySet($response->json(), true);
            }

            // Decode and verify the token
            $decoded = JWT::decode($token, $this->keys);

            if ($decoded && isset($decoded->sub)) {
                return [
                    'userId' => $decoded->sub,
                    'email' => $decoded->email ?? null,
                    'name' => null, // Apple doesn't provide name in JWT
                    'picture' => null // Apple doesn't provide picture
                ];
            }
        } catch (\Exception $e) {
            Log::error('Apple token verification failed: ' . $e->getMessage());
        }

        return null;
    }
}
