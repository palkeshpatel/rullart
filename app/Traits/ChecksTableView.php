<?php

namespace App\Traits;

use App\Helpers\TenantHelper;

/**
 * Trait to check if a table is a view
 *
 * Views cannot be edited or deleted, so we hide add/edit buttons
 * Views are different per database (Kuwait vs Qatar)
 */
trait ChecksTableView
{
    /**
     * List of views (not tables) per database
     *
     * Kuwait views: productpriceview
     * Qatar views: addressbook, areamaster, customercoupon, customers,
     *              customers_devices, filtermaster, filtervalues,
     *              productpriceview, stores
     */
    protected static $viewList = [
        'kuwait' => [
            'productpriceview',
        ],
        'qatar' => [
            'addressbook',
            'areamaster',
            'customercoupon',
            'customers',
            'customers_devices',
            'filtermaster',
            'filtervalues',
            'productpriceview',
            'stores',
        ],
    ];

    /**
     * Check if a table name is actually a view for the current database
     *
     * @param string $tableName
     * @return bool
     */
    public static function isTableView(string $tableName): bool
    {
        // Get current tenant (Kuwait or Qatar)
        $tenant = TenantHelper::getTenantName();

        // Determine which database we're using
        $dbKey = 'kuwait';
        if (TenantHelper::isQatar()) {
            $dbKey = 'qatar';
        }

        // Get views for current database
        $views = self::$viewList[$dbKey] ?? [];

        // Check if table is in the view list
        return in_array(strtolower($tableName), array_map('strtolower', $views));
    }

    /**
     * Get all view names for current database
     *
     * @return array
     */
    public static function getViewList(): array
    {
        $dbKey = 'kuwait';
        if (TenantHelper::isQatar()) {
            $dbKey = 'qatar';
        }

        return self::$viewList[$dbKey] ?? [];
    }
}
