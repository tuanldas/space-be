<?php

namespace App\Adapters\TokenGenerator;

use Illuminate\Support\Facades\Http;

class PassportTokenGenerator implements TokenGeneratorInterface
{
    public function generate(string $username, string $password): string
    {
        $response = Http::post(config('services.passport.url') . '/oauth/token', [
            'grant_type' => 'password',
            'client_id' => config('services.passport.client_id'),
            'client_secret' => config('services.passport.client_secret'),
            'username' => $username,
            'password' => $password,
        ]);

        return $response->json();
    }
}
