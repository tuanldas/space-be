<?php

namespace Tests\Feature\Api;

use App\Adapters\Interfaces\OAuthAdapterInterface;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Passport;
use Mockery;
use Tests\TestCase;

class AuthCookieTest extends TestCase
{
    use RefreshDatabase;
    
    protected $oauthMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->oauthMock = Mockery::mock(OAuthAdapterInterface::class);
        $this->app->instance(OAuthAdapterInterface::class, $this->oauthMock);
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_login_should_set_cookie(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $this->oauthMock->shouldReceive('getTokenByPassword')
            ->once()
            ->with('test@example.com', 'password123')
            ->andReturn([
                'access_token' => 'mocked-access-token',
                'refresh_token' => 'mocked-refresh-token',
                'expires_in' => 3600,
                'token_type' => 'Bearer'
            ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'user',
                ])
                ->assertJsonMissing(['access_token', 'refresh_token'])
                ->assertCookie('access_token')
                ->assertCookie('refresh_token');

        $response->assertJson([
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
            ]
        ]);
    }

    public function test_api_accepts_token_from_cookie(): void
    {
        $user = User::factory()->create();
        
        Passport::actingAs($user);
        
        $response = $this->withCookie('access_token', 'test-token')
                        ->getJson('/api/me');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'id', 'name', 'email'
                ]);
                
        $response->assertJson([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    public function test_logout_clears_cookies(): void
    {
        $user = User::factory()->create();
        
        Passport::actingAs($user);
        
        $this->mock(\App\Services\Interfaces\AuthServiceInterface::class)
            ->shouldReceive('logout')
            ->once()
            ->andReturn(['message' => __('auth.logout_success')]);

        $response = $this->postJson('/api/logout');

        $response->assertStatus(200);
        
        $this->assertTrue($response->headers->has('Set-Cookie'));
    }
} 
