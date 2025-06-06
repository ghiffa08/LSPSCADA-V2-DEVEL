<?php

namespace App\Services;

use Google_Client;
use LogicException;

class GoogleOAuthService
{
    protected Google_Client $client;

    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setClientId(getenv('GOOGLE_CLIENT_ID'));
        $this->client->setClientSecret(getenv('GOOGLE_CLIENT_SECRET'));
        $this->client->setRedirectUri(getenv('GOOGLE_REDIRECT_URI'));
        $this->client->addScope('email');
        $this->client->addScope('profile');
    }

    public function getAuthUrl(): string
    {
        return $this->client->createAuthUrl();
    }

    public function fetchUserFromCode(string $code): ?array
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($code);
        if (isset($token['error'])) {
            throw new LogicException('Google login gagal: ' . ($token['error_description'] ?? $token['error']));
        }
        // Pastikan id_token tersedia
        if (empty($token['id_token'])) {
            throw new LogicException('Google login gagal: id_token tidak tersedia.');
        }
        $this->client->setAccessToken($token);
        $payload = $this->client->verifyIdToken($token['id_token']);
        if (!$payload) {
            throw new LogicException('Google login gagal: id_token tidak valid.');
        }
        return $payload;
    }
}
