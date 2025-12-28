<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get locale from URL segment or session
        $locale = $request->segment(1);
        
        // Validate locale
        if (!in_array($locale, ['en', 'ar'])) {
            $locale = Session::get('locale', App::getLocale() ?: 'en');
        }
        
        // Set locale
        App::setLocale($locale);
        
        // Only set session if it's a web request (not during route caching)
        if ($request->hasSession()) {
            Session::put('locale', $locale);
        }
        
        return $next($request);
    }
}

