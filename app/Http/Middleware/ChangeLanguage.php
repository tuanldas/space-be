<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class ChangeLanguage
{
    public function handle(Request $request, Closure $next): Response
    {
        $language = $request->header('Accept-Language');
        if ($language) {
            App::setLocale($language);
        }
        return $next($request);
    }
}
