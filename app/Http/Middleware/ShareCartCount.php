<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Share cart count and wishlist count with views
 * This runs AFTER StartSession middleware, so session is fully loaded
 */
class ShareCartCount
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Log::info('=== SHARE CART COUNT MIDDLEWARE START ===', [
            'has_session' => $request->hasSession(),
            'session_id' => $request->hasSession() ? $request->session()->getId() : 'NO_SESSION',
            'url' => $request->url(),
        ]);
        
        // IMPORTANT: Share cart count BEFORE calling $next() so views can use it
        // This runs AFTER StartSession middleware (which is in web group)
        // So session is already loaded from database
        if ($request->hasSession()) {
            $cartCount = $this->getCartCount();
            $wishlistCount = $this->getWishlistCount();
            
            Log::info('=== SHARE CART COUNT MIDDLEWARE === Sharing with views BEFORE response', [
                'cartCount' => $cartCount,
                'wishlistCount' => $wishlistCount,
                'session_id' => Session::getId(),
                'shoppingcartid' => Session::get('shoppingcartid'),
            ]);
            
            // Share with all views BEFORE response is created
            // This ensures views can access cartCount when they render
            View::share([
                'cartCount' => $cartCount,
                'wishlistCount' => $wishlistCount,
            ]);
        } else {
            Log::warning('=== SHARE CART COUNT MIDDLEWARE === No session available', [
                'url' => $request->url(),
            ]);
        }
        
        $response = $next($request);
        
        return $response;
    }
    
    protected function getCartCount()
    {
        $shoppingCartId = Session::get('shoppingcartid');
        $sessionId = Session::getId();
        
        Log::info('=== SHARE CART COUNT: getCartCount() START ===', [
            'shoppingcartid_from_session' => $shoppingCartId,
            'session_id' => $sessionId,
            'session_has_shoppingcartid' => Session::has('shoppingcartid'),
            'all_session_keys' => array_keys(Session::all()),
        ]);
        
        if ($shoppingCartId) {
            try {
                $count = DB::table('shoppingcartitems')
                    ->where('fkcartid', $shoppingCartId)
                    ->count();
                    
                Log::info('=== SHARE CART COUNT: Found by shoppingcartid ===', [
                    'shoppingcartid' => $shoppingCartId,
                    'count' => $count,
                ]);
                
                return $count;
            } catch (\Exception $e) {
                Log::error('Error getting cart count by shoppingcartid: ' . $e->getMessage(), [
                    'shoppingcartid' => $shoppingCartId,
                    'error' => $e->getMessage(),
                ]);
                return 0;
            }
        }

        // If no cart ID in session, try to find cart by session ID
        if ($sessionId) {
            try {
                Log::info('=== SHARE CART COUNT: Looking up cart by session ID ===', [
                    'session_id' => $sessionId,
                ]);
                
                $cart = DB::table('shoppingcartmaster')
                    ->where('sessionid', $sessionId)
                    ->where('fkcustomerid', 0)
                    ->first();
                    
                if ($cart) {
                    Log::info('=== SHARE CART COUNT: Found cart by session ID ===', [
                        'session_id' => $sessionId,
                        'cartid' => $cart->cartid,
                    ]);
                    
                    Session::put('shoppingcartid', $cart->cartid);
                    $count = DB::table('shoppingcartitems')
                        ->where('fkcartid', $cart->cartid)
                        ->count();
                        
                    Log::info('=== SHARE CART COUNT: Cart count from database ===', [
                        'cartid' => $cart->cartid,
                        'count' => $count,
                    ]);
                    
                    return $count;
                } else {
                    Log::warning('=== SHARE CART COUNT: No cart found by session ID ===', [
                        'session_id' => $sessionId,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Error finding cart by session ID: ' . $e->getMessage(), [
                    'session_id' => $sessionId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        Log::info('=== SHARE CART COUNT: Returning 0 ===', [
            'shoppingcartid' => $shoppingCartId,
            'session_id' => $sessionId,
        ]);
        
        return 0;
    }
    
    protected function getWishlistCount()
    {
        $customerId = Session::get('customerid', 0);
        if ($customerId > 0) {
            try {
                return DB::table('wishlist')
                    ->where('fkcustomerid', $customerId)
                    ->count();
            } catch (\Exception $e) {
                return 0;
            }
        }
        return 0;
    }
}
