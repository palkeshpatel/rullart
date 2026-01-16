<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure session database connection is set to the correct tenant database
 * This MUST run BEFORE StartSession middleware
 */
class EnsureSessionDatabase
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Ensure session connection uses the same database as mysql connection
        // This is critical for multi-tenant setups where database switching happens
        try {
            $mysqlDb = config('database.connections.mysql.database');
            $sessionDb = config('database.connections.session.database');
            
            if ($mysqlDb && $mysqlDb !== $sessionDb) {
                // Update session connection database to match mysql connection
                Config::set('database.connections.session.database', $mysqlDb);
                
                // If session connection is already initialized, reconnect it
                try {
                    $sessionConnection = DB::connection('session');
                    if ($sessionConnection->getDatabaseName() !== $mysqlDb) {
                        // Don't purge if session might be active - just update config
                        // The connection will use the new database on next query
                        Config::set('database.connections.session.database', $mysqlDb);
                    }
                } catch (\Exception $e) {
                    // Connection not initialized yet - that's fine
                }
            }
        } catch (\Exception $e) {
            // Log but don't break the request
            Log::warning('Failed to sync session database: ' . $e->getMessage());
        }
        
        return $next($request);
    }
}
