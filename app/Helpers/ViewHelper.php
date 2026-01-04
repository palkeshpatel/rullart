<?php

namespace App\Helpers;

use App\Traits\ChecksTableView;

/**
 * Helper class for checking if a table is a view
 * Can be used in Blade templates
 */
class ViewHelper
{
    use ChecksTableView;

    /**
     * Check if table is a view (static method for Blade)
     * 
     * @param string $tableName
     * @return bool
     */
    public static function isView(string $tableName): bool
    {
        return self::isTableView($tableName);
    }
}

