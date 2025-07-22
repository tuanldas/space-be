<?php

namespace App\Adapters;

use App\Adapters\Interfaces\OAuthAdapterInterface;
use Illuminate\Support\Facades\Http;

class OAuthAdapter implements OAuthAdapterInterface
{
    protected function getOAuthUrl(): string
    {
        $baseUrl = config('oauth.server_url', config('app.url'));
        
        return rtrim($baseUrl, '/') . '/oauth/token';
    }

    /**
     * @inheritDoc
     */
    public function getTokenByPassword(string $username, string $password): array
    {
        $response = Http::asForm()->post($this->getOAuthUrl(), [
            'grant_type' => 'password',
            'client_id' => config('passport.password_grant_client.id'),
            'client_secret' => config('passport.password_grant_client.secret'),
            'username' => $username,
            'password' => $password,
            'scope' => '',
        ]);

        return $response->json();
    }

    /**
     * @inheritDoc
     */
    public function getTokenByRefreshToken(string $refreshToken): array
    {
        $response = Http::asForm()->post($this->getOAuthUrl(), [
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
            'client_id' => config('passport.password_grant_client.id'),
            'client_secret' => config('passport.password_grant_client.secret'),
            'scope' => '',
        ]);

        return $response->json();
    }
} 