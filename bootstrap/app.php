<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function () {
            // Load API routes FIRST (before frontend routes) to avoid route conflicts
            if (file_exists(base_path('routes/api.php'))) {
                Route::middleware('web')
                    ->group(base_path('routes/api.php'));
            }
            if (file_exists(base_path('routes/admin.php'))) {
                Route::middleware('web')
                    ->group(base_path('routes/admin.php'));
            }
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\Admin::class,
            'locale' => \App\Http\Middleware\SetLocale::class,
            'currency' => \App\Http\Middleware\SetCurrency::class,
        ]);
        
        // CRITICAL: Ensure session database is set BEFORE StartSession middleware runs
        // This must have higher priority than StartSession (which is in web middleware group)
        $middleware->prepend(\App\Http\Middleware\EnsureSessionDatabase::class);
        
        // Share cart count AFTER StartSession middleware
        // Add to web middleware group so it runs after StartSession but before controller
        // Note: Using validateCsrfToken priority as reference (StartSession is in web group)
        $middleware->append(\App\Http\Middleware\ShareCartCount::class);
        
        // Add session debugging middleware BEFORE web middleware (which includes StartSession)
        // This helps debug session connection issues
        // Note: Using env() directly since app() is not available during bootstrap
        if (env('APP_ENV') === 'local' || env('APP_DEBUG', false)) {
            $middleware->prepend(\App\Http\Middleware\LogSessionActivity::class);
        }
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();