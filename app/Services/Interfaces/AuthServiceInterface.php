<?php

namespace App\Services\Interfaces;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

interface AuthServiceInterface
{
    /**
     * Register a new user.
     * 
     * @param array $data
     * @return array
     */
    public function register(array $data): array;

    /**
     * Login user and get tokens.
     * 
     * @param array $credentials
     * @return array
     * @throws ValidationException
     */
    public function login(array $credentials): array;

    /**
     * Refresh access token.
     * 
     * @param string $refreshToken
     * @return array
     */
    public function refreshToken(string $refreshToken): array;

    /**
     * Logout user.
     * 
     * @param Request $request
     * @return array
     */
    public function logout(Request $request): array;
} 