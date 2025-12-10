<?php

/**
 * Script to check and create all missing tables from CI database
 * This will fix all table issues at once
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// CI Database configuration
$ciDb = new PDO(
    "mysql:host=127.0.0.1;dbname=rullart_rullart;charset=utf8mb4",
    'root',
    '',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

echo "Checking and fixing all missing tables...\n\n";

// Core tables that must exist
$coreTables = [
    'customers',
    'ordermaster',
    'orderitems',
    'products',
    'category',
    'returnrequest',
    'productrating',
    'shoppingcart',
    'admin',
];

$fixed = 0;
$existsCount = 0;

foreach ($coreTables as $tableName) {
    try {
        // Check if table exists in Laravel database
        $tableExists = DB::select("SHOW TABLES LIKE '{$tableName}'");
        
        if (empty($tableExists)) {
            echo "❌ Missing: {$tableName}\n";
            
            // Get table structure from CI database
            $createTable = $ciDb->query("SHOW CREATE TABLE `{$tableName}`")->fetch(PDO::FETCH_ASSOC);
            
            if ($createTable) {
                $createSql = $createTable['Create Table'];
                
                // Execute the CREATE TABLE statement
                DB::statement($createSql);
                
                echo "   ✅ Created successfully!\n";
                $fixed++;
                
                // Try to copy data
                try {
                    $data = $ciDb->query("SELECT * FROM `{$tableName}`")->fetchAll(PDO::FETCH_ASSOC);
                    if (!empty($data)) {
                        $columns = array_keys($data[0]);
                        $columnsStr = '`' . implode('`, `', $columns) . '`';
                        
                        foreach (array_chunk($data, 100) as $chunk) {
                            $values = [];
                            foreach ($chunk as $row) {
                                $rowValues = [];
                                foreach ($columns as $col) {
                                    $value = $row[$col];
                                    if ($value === null) {
                                        $rowValues[] = 'NULL';
                                    } elseif ($value === '0000-00-00 00:00:00' || $value === '0000-00-00') {
                                        // Handle invalid MySQL datetime/date values
                                        $rowValues[] = 'NULL';
                                    } else {
                                        $rowValues[] = DB::getPdo()->quote($value);
                                    }
                                }
                                $values[] = '(' . implode(', ', $rowValues) . ')';
                            }
                            
                            $valuesStr = implode(', ', $values);
                            $insertSql = "INSERT INTO `{$tableName}` ({$columnsStr}) VALUES {$valuesStr}";
                            DB::statement($insertSql);
                        }
                        
                        echo "   📋 Copied " . count($data) . " records\n";
                    }
                } catch (\Exception $e) {
                    echo "   ⚠️  Could not copy data: " . $e->getMessage() . "\n";
                }
            } else {
                echo "   ⚠️  Could not get table structure from CI database\n";
            }
        } else {
            echo "✅ Exists: {$tableName}\n";
            $existsCount++;
        }
    } catch (\Exception $e) {
        echo "❌ Error checking {$tableName}: " . $e->getMessage() . "\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Summary:\n";
echo "  ✅ Tables that exist: {$existsCount}\n";
echo "  🔧 Tables fixed: {$fixed}\n";
echo "\nAll core tables should now be available!\n";

