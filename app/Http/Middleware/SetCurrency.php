<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Country;
use Symfony\Component\HttpFoundation\Response;

class SetCurrency
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only process if we have a session (skip during route caching)
        if ($request->hasSession()) {
            // Only set if not already in session
            if (!Session::has('currencycode')) {
                $defaultCountry = config('app.default_country', 'Kuwait');
                $defaultCurrency = config('app.default_currencycode', 'KWD');
                
                // Simplified: Use default for now, can enhance with IP detection later
                $countryName = Session::get('ip_countryName', $defaultCountry);
                
                $country = Country::where('countryname', $countryName)
                    ->where('isactive', 1)
                    ->first();
                
                if ($country) {
                    Session::put('currencycode', $country->currencycode);
                    Session::put('currencyrate', $country->currencyrate);
                } else {
                    Session::put('currencycode', $defaultCurrency);
                    Session::put('currencyrate', 1);
                }
                
                Session::put('currencytimestamp', time());
            }
        }
        
        return $next($request);
    }
}

