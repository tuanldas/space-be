<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AddCookieToRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $accessToken = $request->cookie('access_token');
        
        if ($accessToken) {
            // Đảm bảo header Authorization luôn được đặt nếu có cookie access_token
            $request->headers->set('Authorization', 'Bearer ' . $accessToken);
            
            // Debug log trong môi trường local
            if (config('app.env') === 'local') {
                Log::info('Cookie Authentication: Token found and set in Authorization header');
            }
        } else if (config('app.env') === 'local') {
            Log::info('Cookie Authentication: No token found in cookies');
        }
        
        return $next($request);
    }
} 