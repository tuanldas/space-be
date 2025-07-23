<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RefreshTokenRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Services\Interfaces\AuthServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    protected $authService;

    /**
     * AuthController constructor.
     * 
     * @param AuthServiceInterface $authService
     */
    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new user.
     * 
     * @param RegisterRequest $request
     * @return JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json($result, 201);
    }

    /**
     * Login user and set tokens as cookies.
     * 
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login($request->validated());
        
        $accessTokenExpiration = isset($result['expires_in']) ? ceil($result['expires_in'] / 60) : 60 * 24 * 15;
        $refreshTokenExpiration = $accessTokenExpiration * 2;
        
        $response = response()->json([
            'message' => $result['message'],
            'user' => $result['user']
        ]);
        
        $response->cookie(
            'access_token', 
            $result['access_token'], 
            $accessTokenExpiration,
            null, 
            null, 
            config('app.env') !== 'local',
            true
        );
        
        $response->cookie(
            'refresh_token', 
            $result['refresh_token'], 
            $refreshTokenExpiration,
            null, 
            null, 
            config('app.env') !== 'local',
            true
        );
        
        return $response;
    }

    /**
     * Refresh access token.
     * 
     * @param RefreshTokenRequest $request
     * @return JsonResponse
     */
    public function refreshToken(RefreshTokenRequest $request): JsonResponse
    {
        $refreshToken = $request->refresh_token ?? $request->cookie('refresh_token');
        $result = $this->authService->refreshToken($refreshToken);
        
        $accessTokenExpiration = isset($result['expires_in']) ? ceil($result['expires_in'] / 60) : 60 * 24 * 15;
        $refreshTokenExpiration = $accessTokenExpiration * 2;
        
        $response = response()->json([
            'message' => $result['message']
        ]);
        
        $response->cookie(
            'access_token', 
            $result['access_token'], 
            $accessTokenExpiration,
            null, 
            null, 
            config('app.env') !== 'local',
            true
        );
        
        $response->cookie(
            'refresh_token', 
            $result['refresh_token'], 
            $refreshTokenExpiration,
            null, 
            null, 
            config('app.env') !== 'local',
            true
        );
        
        return $response;
    }

    /**
     * Logout user.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $result = $this->authService->logout($request);
        
        $response = response()->json($result);
        
        $response->cookie('access_token', '', -1);
        $response->cookie('refresh_token', '', -1);
        
        return $response;
    }
    
    /**
     * Get current user's information with roles and abilities.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoles(),
            'abilities' => $user->getAbilities()->pluck('name'),
        ]);
    }
} 