<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class CompressTokenMiddleware
{
    protected $keys = [
        'access_token',
        'refresh_token',
    ];

    public function handle(Request $request, Closure $next)
    {
        // Giải mã và giải nén token trong request cookies
        foreach ($this->keys as $key) {
            if ($request->hasCookie($key)) {
                try {
                    $encryptedToken = $request->cookie($key);

                    if (!$this->isEncrypted($encryptedToken)) {
                        continue;
                    }
                    $compressedToken = Crypt::decrypt($encryptedToken);
                    $token = gzdecode($compressedToken);
                    $request->merge([$key => $token]);
                } catch (\Exception $e) {
                    return response()->json(['error' => 'Invalid or corrupted token: ' . $key], 400);
                }
            }
        }

        // Tiếp tục xử lý request
        $response = $next($request);

        // Nén và mã hóa token trong response cookies
        foreach ($response->headers->getCookies() as $cookie) {
            if (!in_array($cookie->getName(), $this->keys)) {
                continue;
            }
            $token = $cookie->getValue();
            $compressedToken = gzencode($token, 9);
            $encryptedToken = Crypt::encrypt($compressedToken);

            $response->headers->setCookie($cookie->withValue($encryptedToken));
        }
        return $response;
    }

    protected function isEncrypted($value): bool
    {
        try {
            Crypt::decrypt($value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
