<?php

namespace App\Helpers;

class PriceHelper
{
    /**
     * Format price with currency
     */
    public static function format($price, $currencyCode = 'KWD', $currencyRate = 1)
    {
        $convertedPrice = $price * $currencyRate;
        
        // Format for KWD (3 decimals)
        if ($currencyCode === 'KWD') {
            return number_format($convertedPrice, 3, '.', '');
        }
        
        return number_format($convertedPrice, 2, '.', '');
    }
    
    /**
     * Show price with currency symbol
     */
    public static function show($price, $currencyCode = 'KWD', $currencyRate = 1)
    {
        $formatted = self::format($price, $currencyCode, $currencyRate);
        return $formatted . ' ' . $currencyCode;
    }
}

