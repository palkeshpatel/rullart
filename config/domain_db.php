<?php

/**
 * Domain/Port to Database Mapping Configuration
 *
 * This configuration maps domains (production) or ports (local) to their
 * corresponding database names for multi-tenant database switching.
 *
 * Structure:
 * - 'local': Maps ports to database names (for local development)
 * - 'production': Maps domain names to database names (for production)
 *
 * Usage:
 * - Local: http://localhost:8000 -> rullart_rullart_kuwaitbeta
 * - Local: http://localhost:9000 -> rullart_rullart_qatarbeta
 * - Production: https://betakuwait.techiebrothers.in -> rullart_rullart_kuwaitbeta
 * - Production: https://betaqatar.techiebrothers.in -> rullart_rullart_qatarbeta
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Local Development Mapping (Port-based)
    |--------------------------------------------------------------------------
    |
    | Maps local development ports to their corresponding databases.
    | Used when APP_ENV=local
    |
    */
    'local' => [
        '8000' => 'rullart_rullart_kuwaitbeta',
        '9000' => 'rullart_rullart_qatarbeta',
    ],

    /*
    |--------------------------------------------------------------------------
    | Production Mapping (Domain-based)
    |--------------------------------------------------------------------------
    |
    | Maps production domain names to their corresponding databases.
    | Used when APP_ENV=production
    |
    */
    'production' => [
        'betakuwait.techiebrothers.in' => 'techiebrothers_betakuwait',
        'betaqatar.techiebrothers.in'  => 'techiebrothers_betaqatar',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Database (Fallback)
    |--------------------------------------------------------------------------
    |
    | Default database to use if no mapping is found.
    | This should match your .env DB_DATABASE value.
    |
    */
    'default' => env('DB_DATABASE', 'rullart_rullart_kuwaitbeta'),

    /*
    |--------------------------------------------------------------------------
    | Enable Database Switching
    |--------------------------------------------------------------------------
    |
    | Set to false to disable automatic database switching.
    | Useful for debugging or when you want to use .env database only.
    |
    */
    'enabled' => env('DB_SWITCHING_ENABLED', true),

];