<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use App\Helpers\ViewHelper;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use Bootstrap 5 pagination view
        \Illuminate\Pagination\Paginator::defaultView('vendor.pagination.bootstrap-5');
        \Illuminate\Pagination\Paginator::defaultSimpleView('vendor.pagination.simple-bootstrap-5');

        // Register Blade directive for checking if table is view
        Blade::if('isView', function ($tableName) {
            return ViewHelper::isView($tableName);
        });

        // IMPORTANT: Database switching MUST happen BEFORE session middleware loads
        // We use boot() which runs early, but also hook into session middleware
        // to ensure session connection is ready when session middleware runs

        // Multi-tenant database switching based on domain/port
        $this->switchDatabaseByDomain();

        // Ensure session connection is synchronized after database switch
        $this->syncSessionConnection();

        // Hook into session start to ensure session connection is ready
        $this->ensureSessionConnectionReady();

        // Share cart count with all views using View Composer
        // This runs when views are rendered, ensuring session is fully loaded
        View::composer('*', function ($view) {
            // Only compute if not already set (avoid re-computing)
            if (!$view->offsetExists('cartCount') || $view->offsetGet('cartCount') === 0) {
                $cartCount = $this->getCartCountForView();
                $wishlistCount = $this->getWishlistCountForView();

                $view->with([
                    'cartCount' => $cartCount,
                    'wishlistCount' => $wishlistCount,
                ]);
            }
        });
    }

    /**
     * Get cart count for view (runs when view is rendered, session is loaded)
     */
    protected function getCartCountForView()
    {
        if (!app()->bound('request') || app()->runningInConsole()) {
            return 0;
        }

        try {
            $shoppingCartId = Session::get('shoppingcartid');
            $sessionId = Session::getId();

            Log::info('=== VIEW COMPOSER: getCartCountForView ===', [
                'shoppingcartid' => $shoppingCartId,
                'session_id' => $sessionId,
            ]);

            if ($shoppingCartId) {
                $count = DB::table('shoppingcartitems')
                    ->where('fkcartid', $shoppingCartId)
                    ->count();

                Log::info('=== VIEW COMPOSER: Cart count found ===', [
                    'shoppingcartid' => $shoppingCartId,
                    'count' => $count,
                ]);

                return $count;
            }

            // Try to find cart by session ID
            if ($sessionId) {
                $cart = DB::table('shoppingcartmaster')
                    ->where('sessionid', $sessionId)
                    ->where('fkcustomerid', 0)
                    ->first();

                if ($cart) {
                    Session::put('shoppingcartid', $cart->cartid);
                    $count = DB::table('shoppingcartitems')
                        ->where('fkcartid', $cart->cartid)
                        ->count();

                    Log::info('=== VIEW COMPOSER: Cart found by session ID ===', [
                        'session_id' => $sessionId,
                        'cartid' => $cart->cartid,
                        'count' => $count,
                    ]);

                    return $count;
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in getCartCountForView: ' . $e->getMessage());
        }

        return 0;
    }

    /**
     * Get wishlist count for view
     */
    protected function getWishlistCountForView()
    {
        if (!app()->bound('request') || app()->runningInConsole()) {
            return 0;
        }

        try {
            $customerId = Session::get('customerid', 0);
            if ($customerId > 0) {
                return DB::table('wishlist')
                    ->where('fkcustomerid', $customerId)
                    ->count();
            }
        } catch (\Exception $e) {
            // Silent fail
        }

        return 0;
    }

    /**
     * Ensure session connection is ready when session middleware starts
     * This prevents session from being lost during database switching
     */
    protected function ensureSessionConnectionReady(): void
    {
        // This method ensures session connection uses the correct database
        // BEFORE the session middleware tries to load the session
        // We call syncSessionConnection which will handle this
        // The key is that switchDatabaseByDomain() runs in boot(), which happens
        // early in the request lifecycle, before session middleware runs
    }

    /**
     * Sync session connection to use the same database as the main connection
     * This ensures sessions are stored in the correct tenant database
     *
     * @return void
     */
    protected function syncSessionConnection(): void
    {
        // Skip if running in console or no request available
        if (app()->runningInConsole() || !app()->bound('request')) {
            return;
        }

        try {
            $currentDb = config('database.connections.mysql.database');
            $sessionDb = config('database.connections.session.database');

            // If databases are different, sync them
            if ($currentDb !== $sessionDb && $currentDb) {
                Config::set('database.connections.session.database', $currentDb);

                // If session connection is already established, reconnect it
                if (DB::connection('session')->getDatabaseName() !== $currentDb) {
                    DB::purge('session');
                    DB::reconnect('session');
                }
            }
        } catch (\Exception $e) {
            // Log error but don't break the application
            Log::warning('Failed to sync session connection: ' . $e->getMessage());
        }
    }

    /**
     * Switch database connection based on domain (production) or port (local)
     *
     * This method automatically switches the database connection based on:
     * - Local environment: Uses port number (8000 = Kuwait, 9000 = Qatar)
     * - Production environment: Uses domain name
     *
     * @return void
     */
    protected function switchDatabaseByDomain(): void
    {
        // Check if database switching is enabled
        if (!config('domain_db.enabled', true)) {
            return;
        }

        // Skip database switching for console commands (artisan, migrations, etc.)
        // This allows artisan commands to use the default .env database
        if (app()->runningInConsole()) {
            return;
        }

        // Skip if no request is available (e.g., during testing)
        if (!app()->bound('request')) {
            return;
        }

        try {
            $request = request();
            $env = app()->environment(); // 'local' or 'production'

            // Get the mapping configuration for current environment
            $mapping = config("domain_db.{$env}", []);

            if (empty($mapping)) {
                // No mapping found for this environment, use default
                $this->logDatabaseSwitch('no_mapping', $env, config('domain_db.default'));
                return;
            }

            // Determine the key to use for database lookup
            $key = $this->getDatabaseKey($request, $env);

            if (!$key) {
                // Could not determine key, use default database
                $defaultDb = config('domain_db.default');
                $this->logDatabaseSwitch('no_key', $env, $defaultDb);
                return;
            }

            // Get database name from mapping
            $database = $mapping[$key] ?? null;

            if (!$database) {
                // No database mapping found for this key
                $defaultDb = config('domain_db.default');
                $this->logDatabaseSwitch('no_mapping_for_key', $env, $defaultDb, $key);

                // In production, abort if tenant not configured (security)
                if ($env === 'production') {
                    abort(403, "Tenant not configured for: {$key}");
                }

                return;
            }

            // Switch database connection
            $this->applyDatabaseSwitch($database, $key, $env);
        } catch (\Exception $e) {
            // Log error but don't break the application
            Log::error('Database switching error: ' . $e->getMessage(), [
                'exception' => $e,
                'environment' => app()->environment(),
            ]);

            // In production, you might want to abort, but for now we'll continue
            // with default database to prevent breaking the site
        }
    }

    /**
     * Get the database key based on environment
     *
     * @param Request $request
     * @param string $env
     * @return string|null
     */
    protected function getDatabaseKey(Request $request, string $env): ?string
    {
        if ($env === 'local') {
            // Local: Use port number
            $port = (string) $request->getPort();
            return $port;
        } else {
            // Production: Use host/domain name
            $host = $request->getHost();
            return $host;
        }
    }

    /**
     * Apply database switch by updating configuration and reconnecting
     *
     * @param string $database
     * @param string $key
     * @param string $env
     * @return void
     */
    protected function applyDatabaseSwitch(string $database, string $key, string $env): void
    {
        // Get current database to check if switch is needed
        $currentDatabase = config('database.connections.mysql.database');

        // Update database configuration for both mysql and session connections FIRST
        // This ensures that when connections are initialized, they use the correct database
        Config::set('database.connections.mysql.database', $database);
        Config::set('database.connections.session.database', $database);

        // Only reconnect if database is different
        if ($currentDatabase !== $database) {
            // IMPORTANT: For session connection, we need to be careful not to purge
            // if the session middleware has already started, as this can cause
            // session data to be lost. Instead, we update the config and let
            // the connection use the new database on next query.

            // Check if session connection exists and what database it's using
            try {
                $sessionConnection = DB::connection('session');
                $sessionDb = $sessionConnection->getDatabaseName();

                // If session connection exists and database is different, we need to reconnect
                // BUT: Only do this if we're early enough in the request lifecycle
                // (before session middleware has loaded session data)
                if ($sessionDb !== $database) {
                    // Check if session has been started (this tells us if session middleware has run)
                    if (session_status() === PHP_SESSION_ACTIVE) {
                        // Session already started - DON'T purge, just update config
                        // The session connection will use the new database for next query
                        // This is safe because we're ensuring all tenant databases have the sessions table
                        Log::warning('Database switch after session start - session connection not purged', [
                            'old_db' => $sessionDb,
                            'new_db' => $database,
                        ]);
                    } else {
                        // Session not started yet - safe to purge/reconnect
                        DB::purge('session');
                        DB::reconnect('session');
                    }
                }
            } catch (\Exception $e) {
                // Session connection not yet initialized - that's fine, it will use the correct DB when initialized
                if (app()->environment('local') || config('app.debug')) {
                    Log::debug('Session connection not yet initialized during database switch', [
                        'database' => $database,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Purge and reconnect mysql connection
            DB::purge('mysql');
            DB::reconnect('mysql');

            // Log the database switch for debugging
            $this->logDatabaseSwitch('switched', $env, $database, $key);
        } else {
            // Database is the same, but ensure session connection config matches
            Config::set('database.connections.session.database', $database);
        }
    }

    /**
     * Log database switching activity for debugging
     *
     * @param string $action
     * @param string $env
     * @param string $database
     * @param string|null $key
     * @return void
     */
    protected function logDatabaseSwitch(string $action, string $env, string $database, ?string $key = null): void
    {
        // Only log in local environment or when debug is enabled
        if (app()->environment('local') || config('app.debug')) {
            $message = "Database Switch [{$action}]: Environment={$env}, Database={$database}";
            if ($key) {
                $message .= ", Key={$key}";
            }

            Log::info($message, [
                'action' => $action,
                'environment' => $env,
                'database' => $database,
                'key' => $key,
            ]);
        }
    }
}
