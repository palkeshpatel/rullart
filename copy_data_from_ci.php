<?php

/**
 * Copy data from CI database (rullart_qataralpha) to Laravel database (rullart_qatarbeta_laravel)
 * Run this script: php copy_data_from_ci.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// CI Database Configuration (from CI project)
$ciConfig = [
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'rullart_qataralpha',
    'username' => 'root',
    'password' => '',
];

// Laravel Database Configuration
$laravelConfig = [
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'rullart_qatarbeta_laravel'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
];

echo "Starting data migration from CI to Laravel...\n";
echo "Source: {$ciConfig['database']}\n";
echo "Target: {$laravelConfig['database']}\n\n";

try {
    // Connect to CI database
    $ciConnection = new PDO(
        "mysql:host={$ciConfig['host']};port={$ciConfig['port']};dbname={$ciConfig['database']}",
        $ciConfig['username'],
        $ciConfig['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Tables to copy data from
    $tables = [
        'ordermaster' => 'orderid',
        'orderitems' => 'orderitemid',
        'customers' => 'customerid',
        'products' => 'productid',
        'category' => 'categoryid',
        'occassion' => 'occassionid',
        'productrating' => 'rateid',
        'returnrequest' => 'requestid',
        'shoppingcart' => 'shoppingcartid',
        'wishlist' => 'wishlistid',
        'commonmaster' => 'commonid',
        'countrymaster' => 'countryid',
        'couponcode' => 'couponcodeid',
        'discounts' => 'id',
        'messages' => 'messageid',
        'admin' => 'id',
    ];

    foreach ($tables as $table => $primaryKey) {
        echo "Processing table: {$table}...\n";

        try {
            // Check if table exists in CI database
            $stmt = $ciConnection->query("SHOW TABLES LIKE '{$table}'");
            if ($stmt->rowCount() == 0) {
                echo "  ⚠ Table {$table} does not exist in CI database. Skipping...\n";
                continue;
            }

            // Get count from CI database
            $ciCount = $ciConnection->query("SELECT COUNT(*) FROM `{$table}`")->fetchColumn();
            echo "  Found {$ciCount} records in CI database\n";

            if ($ciCount == 0) {
                echo "  ⚠ No data to copy. Skipping...\n\n";
                continue;
            }

            // Check if table exists in Laravel database
            if (!Schema::hasTable($table)) {
                echo "  ⚠ Table {$table} does not exist in Laravel database. Skipping...\n\n";
                continue;
            }

            // Get existing count in Laravel database
            $laravelCount = DB::table($table)->count();
            echo "  Current records in Laravel: {$laravelCount}\n";

            // Check if we should skip if data already exists
            if ($laravelCount > 0) {
                echo "  ⚠ Table already has data. Do you want to continue? (y/n): ";
                $handle = fopen("php://stdin", "r");
                $line = fgets($handle);
                if (trim($line) !== 'y') {
                    echo "  Skipping...\n\n";
                    continue;
                }
            }

            // Fetch data from CI database
            $ciData = $ciConnection->query("SELECT * FROM `{$table}`")->fetchAll(PDO::FETCH_ASSOC);

            if (empty($ciData)) {
                echo "  ⚠ No data fetched. Skipping...\n\n";
                continue;
            }

            // Insert data into Laravel database in batches
            $batchSize = 100;
            $totalBatches = ceil(count($ciData) / $batchSize);
            $inserted = 0;

            DB::beginTransaction();

            try {
                foreach (array_chunk($ciData, $batchSize) as $batchIndex => $batch) {
                    foreach ($batch as $row) {
                        // Handle invalid datetime values
                        foreach ($row as $key => $value) {
                            if (is_string($value) && (
                                $value === '0000-00-00 00:00:00' ||
                                $value === '0000-00-00' ||
                                preg_match('/^\d{4}-\d{2}-\d{2} 00:00:00$/', $value)
                            )) {
                                $row[$key] = null;
                            }
                        }

                        try {
                            DB::table($table)->insert($row);
                            $inserted++;
                        } catch (\Exception $e) {
                            // Skip duplicate entries
                            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                                continue;
                            }
                            throw $e;
                        }
                    }
                    echo "  Processed batch " . ($batchIndex + 1) . "/{$totalBatches}\n";
                }

                DB::commit();
                echo "  ✅ Successfully copied {$inserted} records\n\n";
            } catch (\Exception $e) {
                DB::rollBack();
                echo "  ❌ Error copying data: " . $e->getMessage() . "\n\n";
            }
        } catch (\Exception $e) {
            echo "  ❌ Error processing table {$table}: " . $e->getMessage() . "\n\n";
        }
    }

    echo "\n✅ Data migration completed!\n";
} catch (\Exception $e) {
    echo "\n❌ Fatal error: " . $e->getMessage() . "\n";
    exit(1);
}
