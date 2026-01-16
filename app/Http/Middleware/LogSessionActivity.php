<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LogSessionActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Log before session is accessed
        $cookieName = config('session.cookie');
        $cookieValue = $request->cookie($cookieName);
        $sessionId = $request->hasSession() ? $request->session()->getId() : 'NO_SESSION';
        
        Log::info('=== SESSION MIDDLEWARE START ===', [
            'cookie_name' => $cookieName,
            'cookie_value_received' => $cookieValue ? substr($cookieValue, 0, 50) . '...' : 'NOT_SET',
            'session_id' => $sessionId,
            'session_connection_db' => config('database.connections.session.database'),
            'mysql_connection_db' => config('database.connections.mysql.database'),
            'session_driver' => config('session.driver'),
        ]);
        
        // Note: Cookie value is encrypted, so we can't look it up directly
        // Laravel's StartSession middleware will decrypt it automatically
        
        $response = $next($request);
        
        // Log after session processing
        $newSessionId = $request->hasSession() ? $request->session()->getId() : 'NO_SESSION';
        
        Log::info('=== SESSION MIDDLEWARE END ===', [
            'session_id_before' => $sessionId,
            'session_id_after' => $newSessionId,
            'session_changed' => $sessionId !== $newSessionId,
            'session_has_shoppingcartid' => $request->hasSession() ? $request->session()->has('shoppingcartid') : false,
        ]);
        
        return $response;
    }
}
