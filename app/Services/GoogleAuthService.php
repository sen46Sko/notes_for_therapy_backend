<?php

namespace App\Services;

use Google\Client;

class GoogleAuthService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
        // $this->client->setClientId('532519645180-e8g324p6248b491btpji80p1jc30bjbd.apps.googleusercontent.com');
        $this->client->setClientId('921740965804-kslu1b2sho6f9hg9qps5edmrtiags3ma.apps.googleusercontent.com');
    }

    public function verifyIdToken($idToken)
    {
        $payload = $this->client->verifyIdToken($idToken);

        if ($payload) {
            $userId = $payload['sub'];  // Get user ID from the token
            $email = $payload['email'];  // Get email from the token
            $name = $payload['name'];  // Get name from the token
            $picture = $payload['picture'];  // Get profile picture from the token
            $givenName = $payload['given_name'];  // Get given name from the token
            return [
                'userId' => $userId,
                'email' => $email,
                'name' => $name,
                'picture' => $picture,
                'givenName' => $givenName,
            ];
        }

        return null;  // Return null if token is invalid
    }
}
