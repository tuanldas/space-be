<?php

namespace App\Adapters\Interfaces;

interface OAuthAdapterInterface
{
    /**
     * Get access token using password grant.
     * 
     * @param string $username
     * @param string $password
     * @return array
     */
    public function getTokenByPassword(string $username, string $password): array;

    /**
     * Get access token using refresh token grant.
     * 
     * @param string $refreshToken
     * @return array
     */
    public function getTokenByRefreshToken(string $refreshToken): array;
} 