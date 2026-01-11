<?php

/**
 * Tabby Payment Configuration
 *
 * This configuration matches the CI project's config.php settings for Tabby payment integration.
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Enable Tabby Payment
    |--------------------------------------------------------------------------
    |
    | Set to true to enable Tabby payment plan display on product pages.
    |
    */
    'allow_tabby' => env('TABBY_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Kuwait Tabby Configuration
    |--------------------------------------------------------------------------
    */
    'tabby_public_key_KWT' => env('TABBY_PUBLIC_KEY_KWT', 'pk_443830ab-31a5-4a9c-a14c-4d7dac5ea6a8'),
    'tabby_secret_key_KWT' => env('TABBY_SECRET_KEY_KWT', 'sk_679c9b11-75ae-465f-bbd7-d4363866ec9b'),
    'tabby_merchantcode_KWT' => env('TABBY_MERCHANT_CODE_KWT', 'RLL'),

    /*
    |--------------------------------------------------------------------------
    | Saudi Arabia Tabby Configuration
    |--------------------------------------------------------------------------
    */
    'tabby_public_key_KSA' => env('TABBY_PUBLIC_KEY_KSA', 'pk_443830ab-31a5-4a9c-a14c-4d7dac5ea6a8'),
    'tabby_secret_key_KSA' => env('TABBY_SECRET_KEY_KSA', 'sk_679c9b11-75ae-465f-bbd7-d4363866ec9b'),
    'tabby_merchantcode_KSA' => env('TABBY_MERCHANT_CODE_KSA', 'RLSA'),
];
