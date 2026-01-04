<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

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

        // Multi-tenant database switching based on domain/port
        $this->switchDatabaseByDomain();
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
        
        // Only switch if database is different
        if ($currentDatabase === $database) {
            return;
        }

        // Update database configuration
        Config::set('database.connections.mysql.database', $database);

        // Purge existing connection to force reconnect
        DB::purge('mysql');

        // Reconnect with new database
        DB::reconnect('mysql');

        // Log the database switch for debugging
        $this->logDatabaseSwitch('switched', $env, $database, $key);
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
