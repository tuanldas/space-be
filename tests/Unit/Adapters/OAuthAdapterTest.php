<?php

namespace Tests\Unit\Adapters;

use App\Adapters\OAuthAdapter;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class OAuthAdapterTest extends TestCase
{
    private OAuthAdapter $oauthAdapter;
    private int $clientId;
    private string $clientSecret;
    private string $oauthUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->oauthAdapter = new OAuthAdapter();
        
        $this->clientId = config('passport.password_grant_client.id');
        $this->clientSecret = config('passport.password_grant_client.secret');
        
        Config::set('passport.password_grant_client.id', 123);
        Config::set('passport.password_grant_client.secret', 'test-secret');
        
        $this->oauthUrl = 'http://test-oauth-server/oauth/token';
        Config::set('oauth.server_url', 'http://test-oauth-server');
    }

    public function test_get_token_by_password(): void
    {
        $username = 'test@example.com';
        $password = 'Password123!';
        $expectedResponse = [
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'access_token' => 'mock_access_token',
            'refresh_token' => 'mock_refresh_token',
        ];

        Http::fake([
            $this->oauthUrl => Http::response($expectedResponse)
        ]);

        $result = $this->oauthAdapter->getTokenByPassword($username, $password);

        $this->assertEquals($expectedResponse, $result);
        
        Http::assertSent(function ($request) use ($username, $password) {
            return $request->url() == $this->oauthUrl &&
                   $request->method() == 'POST' &&
                   $request['grant_type'] == 'password' &&
                   $request['client_id'] == 123 &&
                   $request['client_secret'] == 'test-secret' &&
                   $request['username'] == $username &&
                   $request['password'] == $password;
        });
    }

    public function test_get_token_by_refresh_token(): void
    {
        $refreshToken = 'existing_refresh_token';
        $expectedResponse = [
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'access_token' => 'new_access_token',
            'refresh_token' => 'new_refresh_token',
        ];

        Http::fake([
            $this->oauthUrl => Http::response($expectedResponse)
        ]);

        $result = $this->oauthAdapter->getTokenByRefreshToken($refreshToken);

        $this->assertEquals($expectedResponse, $result);
        
        Http::assertSent(function ($request) use ($refreshToken) {
            return $request->url() == $this->oauthUrl &&
                   $request->method() == 'POST' &&
                   $request['grant_type'] == 'refresh_token' &&
                   $request['client_id'] == 123 &&
                   $request['client_secret'] == 'test-secret' &&
                   $request['refresh_token'] == $refreshToken;
        });
    }
} 