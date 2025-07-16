<?php

namespace App\Services;

use App\Adapters\Interfaces\OAuthAdapterInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\AuthServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService implements AuthServiceInterface
{
    protected $userRepository;
    protected $oauthAdapter;

    /**
     * AuthService constructor.
     * 
     * @param UserRepositoryInterface $userRepository
     * @param OAuthAdapterInterface $oauthAdapter
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        OAuthAdapterInterface $oauthAdapter
    ) {
        $this->userRepository = $userRepository;
        $this->oauthAdapter = $oauthAdapter;
    }

    /**
     * @inheritDoc
     */
    public function register(array $data): array
    {
        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return [
            'message' => __('auth.register_success'),
            'user' => $user
        ];
    }

    /**
     * @inheritDoc
     */
    public function login(array $credentials): array
    {
        $user = $this->userRepository->findByEmail($credentials['email']);

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $tokenData = $this->oauthAdapter->getTokenByPassword(
            $credentials['email'],
            $credentials['password']
        );
        
        // Thêm thông báo thành công
        $tokenData['message'] = __('auth.login_success');
        
        return $tokenData;
    }

    /**
     * @inheritDoc
     */
    public function refreshToken(string $refreshToken): array
    {
        $tokenData = $this->oauthAdapter->getTokenByRefreshToken($refreshToken);
        
        // Thêm thông báo thành công
        $tokenData['message'] = __('auth.refresh_token_success');
        
        return $tokenData;
    }

    /**
     * @inheritDoc
     */
    public function logout(Request $request): array
    {
        $request->user()->token()->revoke();

        return [
            'message' => __('auth.logout_success')
        ];
    }
} 