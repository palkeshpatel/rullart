<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * Tenant Helper Class
 * 
 * Provides helper methods to work with multi-tenant database switching.
 * Use this class to get information about the current tenant/database.
 */
class TenantHelper
{
    /**
     * Get the current database name
     * 
     * @return string
     */
    public static function getCurrentDatabase(): string
    {
        return config('database.connections.mysql.database', '');
    }

    /**
     * Get the current tenant identifier (port for local, domain for production)
     * 
     * @return string|null
     */
    public static function getCurrentTenantKey(): ?string
    {
        $env = app()->environment();
        $request = request();

        if (!$request) {
            return null;
        }

        if ($env === 'local') {
            return (string) $request->getPort();
        }

        return $request->getHost();
    }

    /**
     * Get the tenant name (Kuwait or Qatar) based on current database
     * 
     * @return string
     */
    public static function getTenantName(): string
    {
        $database = self::getCurrentDatabase();

        if (strpos($database, 'kuwait') !== false) {
            return 'Kuwait';
        }

        if (strpos($database, 'qatar') !== false) {
            return 'Qatar';
        }

        return 'Unknown';
    }

    /**
     * Check if current tenant is Kuwait
     * 
     * @return bool
     */
    public static function isKuwait(): bool
    {
        return strpos(self::getCurrentDatabase(), 'kuwait') !== false;
    }

    /**
     * Check if current tenant is Qatar
     * 
     * @return bool
     */
    public static function isQatar(): bool
    {
        return strpos(self::getCurrentDatabase(), 'qatar') !== false;
    }

    /**
     * Get all tenant information as an array
     * 
     * @return array
     */
    public static function getTenantInfo(): array
    {
        return [
            'database' => self::getCurrentDatabase(),
            'tenant_key' => self::getCurrentTenantKey(),
            'tenant_name' => self::getTenantName(),
            'is_kuwait' => self::isKuwait(),
            'is_qatar' => self::isQatar(),
            'environment' => app()->environment(),
        ];
    }
}

