<?php

namespace App\Http\Controllers\Api;

use App\Domain\UseCases\LoginUser\LoginUserInputPort;
use App\Domain\UseCases\LoginUser\LoginUserRequestModel;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function __construct(
        private readonly LoginUserInputPort $loginUserInputPort
    )
    {
    }

    public function login(Request $request)
    {
        $response = $this
            ->loginUserInputPort
            ->handle(new LoginUserRequestModel($request->all()));
        $resource = $response->getResource();

        if (isset($resource['access_token']) && isset($resource['refresh_token'])) {
            $secure = app()->environment('production'); // Chỉ bật secure nếu là production
            $accessTokenCookie = cookie(
                'access_token',
                $resource['access_token'],
                $resource['expires_in'] / 60, // Thời gian sống
                '/',
                null, // Domain mặc định là domain hiện tại
                false, // Secure (true nếu production)
                true, // HttpOnly
                false, // Raw
                'None' // SameSite
            );

            $refreshTokenCookie = cookie(
                'refresh_token',
                $resource['refresh_token'],
                60 * 24 * 30, // 30 ngày
                '/',
                null,
                false,
                true,
                false,
                'None'
            );
            return response()->json(['message' => __('auth.success')])
                ->cookie($accessTokenCookie)
                ->cookie($refreshTokenCookie);
        }

        return $resource
            ->response()
            ->setStatusCode($response->getStatusCode());
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->token();

        if ($token) {
            $token->revoke();
            DB::table('oauth_refresh_tokens')
                ->where('access_token_id', $token->id)
                ->update(['revoked' => true]);
        }

        $response = response()->json(['message' => __('auth.logout')]);
        $response->withCookie(cookie()->forget('access_token'));
        $response->withCookie(cookie()->forget('refresh_token'));
        return $response;
    }
}
