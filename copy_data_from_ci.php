<?php

/**
 * Script to copy data from CI database (rullart_rullart) to Laravel database (laravel_123)
 * Usage: php copy_data_from_ci.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

// CI Database configuration
$ciDbConfig = [
    'host' => '127.0.0.1',
    'database' => 'rullart_rullart',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];

// Laravel Database configuration (from .env)
$laravelDbConfig = [
    'host' => env('DB_HOST', '127.0.0.1'),
    'database' => env('DB_DATABASE', 'laravel_123'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];

echo "Starting data copy from CI database to Laravel database...\n\n";

try {
    // Connect to CI database
    $ciConnection = new PDO(
        "mysql:host={$ciDbConfig['host']};dbname={$ciDbConfig['database']};charset={$ciDbConfig['charset']}",
        $ciDbConfig['username'],
        $ciDbConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    echo "Connected to CI database: {$ciDbConfig['database']}\n";
    echo "Target Laravel database: {$laravelDbConfig['database']}\n\n";
    
    // Get all table names from CI database
    $tablesQuery = $ciConnection->query("SHOW TABLES");
    $tables = $tablesQuery->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Found " . count($tables) . " tables to copy\n\n";
    
    $totalRecords = 0;
    $skippedTables = [];
    
    foreach ($tables as $table) {
        try {
            // Check if table exists in Laravel database
            $tableExists = DB::select("SHOW TABLES LIKE '{$table}'");
            
            if (empty($tableExists)) {
                echo "⚠️  Skipping {$table} - table doesn't exist in Laravel database\n";
                $skippedTables[] = $table;
                continue;
            }
            
            // Get row count from CI database
            $countQuery = $ciConnection->query("SELECT COUNT(*) as count FROM `{$table}`");
            $count = $countQuery->fetch()['count'];
            
            if ($count == 0) {
                echo "⏭️  Skipping {$table} - no data to copy\n";
                continue;
            }
            
            echo "📋 Copying {$table} ({$count} records)... ";
            
            // Truncate Laravel table first (optional - comment out if you want to keep existing data)
            DB::table($table)->truncate();
            
            // Fetch all data from CI database
            $dataQuery = $ciConnection->query("SELECT * FROM `{$table}`");
            $rows = $dataQuery->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($rows)) {
                echo "No data found\n";
                continue;
            }
            
            // Insert data in chunks to avoid memory issues
            $chunkSize = 500;
            $chunks = array_chunk($rows, $chunkSize);
            
            foreach ($chunks as $chunk) {
                // Get column names from first row
                $columns = array_keys($chunk[0]);
                
                // Build insert query
                $values = [];
                $placeholders = [];
                
                foreach ($chunk as $row) {
                    $rowValues = [];
                    foreach ($columns as $col) {
                        $value = $row[$col];
                        if ($value === null) {
                            $rowValues[] = 'NULL';
                        } else {
                            $rowValues[] = DB::getPdo()->quote($value);
                        }
                    }
                    $values[] = '(' . implode(', ', $rowValues) . ')';
                }
                
                $columnsStr = '`' . implode('`, `', $columns) . '`';
                $valuesStr = implode(', ', $values);
                
                $insertSql = "INSERT INTO `{$table}` ({$columnsStr}) VALUES {$valuesStr}";
                
                DB::statement($insertSql);
            }
            
            $totalRecords += $count;
            echo "✅ Done ({$count} records copied)\n";
            
        } catch (\Exception $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
            $skippedTables[] = $table;
        }
    }
    
    echo "\n" . str_repeat("=", 60) . "\n";
    echo "✅ Data copy completed!\n";
    echo "Total records copied: {$totalRecords}\n";
    
    if (!empty($skippedTables)) {
        echo "\n⚠️  Skipped tables (" . count($skippedTables) . "):\n";
        foreach ($skippedTables as $table) {
            echo "   - {$table}\n";
        }
    }
    
    echo "\n";
    
} catch (\Exception $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "Make sure:\n";
    echo "1. CI database '{$ciDbConfig['database']}' exists and is accessible\n";
    echo "2. Laravel database '{$laravelDbConfig['database']}' exists and is accessible\n";
    echo "3. Database credentials are correct\n";
    exit(1);
}

