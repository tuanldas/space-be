<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class SetApiLanguage
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
        $lang = $request->query('lang');
        
        if (!$lang && $request->hasHeader('Accept-Language')) {
            $lang = substr($request->header('Accept-Language'), 0, 2);
        }
        
        $supportedLanguages = config('app.supported_locales', ['vi', 'en']);
        
        if ($lang && in_array($lang, $supportedLanguages)) {
            App::setLocale($lang);
        }
        
        return $next($request);
    }
} 