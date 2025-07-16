<?php

namespace App\Services;

use App\Adapters\Interfaces\OAuthAdapterInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\AuthServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Bouncer;
use Silber\Bouncer\Database\Role;

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

        $defaultRole = Role::where('name', 'user')->first();
        
        if ($defaultRole) {
            Bouncer::assign($defaultRole->name)->to($user);
        }

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
        
        $tokenData['user'] = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoles(),
            'abilities' => $user->getAbilities()->pluck('name'),
        ];
        
        $tokenData['message'] = __('auth.login_success');
        
        return $tokenData;
    }

    /**
     * @inheritDoc
     */
    public function refreshToken(string $refreshToken): array
    {
        $tokenData = $this->oauthAdapter->getTokenByRefreshToken($refreshToken);
        
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