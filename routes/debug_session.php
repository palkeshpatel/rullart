<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;

// Debug route to check session cookie
Route::get('/debug-session', function () {
    $sessionId = Session::getId();
    $cookieName = config('session.cookie');
    $cookie = request()->cookie($cookieName);
    
    return response()->json([
        'session_id' => $sessionId,
        'cookie_name' => $cookieName,
        'cookie_value_from_request' => $cookie ?: 'NOT SET',
        'all_cookies' => request()->cookies->all(),
        'session_data' => Session::all(),
        'has_session' => Session::has('shoppingcartid'),
        'shoppingcartid' => Session::get('shoppingcartid'),
        'session_driver' => config('session.driver'),
        'session_connection' => config('session.connection'),
        'session_domain' => config('session.domain'),
        'session_path' => config('session.path'),
        'session_secure' => config('session.secure'),
        'session_same_site' => config('session.same_site'),
    ]);
});
