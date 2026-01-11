<?php

/**
 * Create Laravel System Tables Only
 * This script creates only the essential Laravel system tables needed
 * to work with existing CI databases directly
 *
 * Usage: php create_laravel_system_tables.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== Create Laravel System Tables ===\n\n";

$database = DB::getDatabaseName();
echo "Current Database: {$database}\n\n";

// Laravel system tables that are required
$systemTables = [
    'migrations' => "
        CREATE TABLE IF NOT EXISTS `migrations` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `migration` varchar(255) NOT NULL,
            `batch` int(11) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
    'cache' => "
        CREATE TABLE IF NOT EXISTS `cache` (
            `key` varchar(255) NOT NULL,
            `value` mediumtext NOT NULL,
            `expiration` int(11) NOT NULL,
            PRIMARY KEY (`key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
    'cache_locks' => "
        CREATE TABLE IF NOT EXISTS `cache_locks` (
            `key` varchar(255) NOT NULL,
            `value` varchar(255) NOT NULL,
            `expiration` int(11) NOT NULL,
            PRIMARY KEY (`key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
    'sessions' => "
        CREATE TABLE IF NOT EXISTS `sessions` (
            `id` varchar(255) NOT NULL,
            `user_id` bigint(20) unsigned DEFAULT NULL,
            `ip_address` varchar(45) DEFAULT NULL,
            `user_agent` text,
            `payload` longtext NOT NULL,
            `last_activity` int(11) NOT NULL,
            PRIMARY KEY (`id`),
            KEY `sessions_user_id_index` (`user_id`),
            KEY `sessions_last_activity_index` (`last_activity`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
    'jobs' => "
        CREATE TABLE IF NOT EXISTS `jobs` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `queue` varchar(255) NOT NULL,
            `payload` longtext NOT NULL,
            `attempts` tinyint(3) unsigned NOT NULL,
            `reserved_at` int(10) unsigned DEFAULT NULL,
            `available_at` int(10) unsigned NOT NULL,
            `created_at` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id`),
            KEY `jobs_queue_index` (`queue`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
    'job_batches' => "
        CREATE TABLE IF NOT EXISTS `job_batches` (
            `id` varchar(255) NOT NULL,
            `name` varchar(255) NOT NULL,
            `total_jobs` int(11) NOT NULL,
            `pending_jobs` int(11) NOT NULL,
            `failed_jobs` int(11) NOT NULL,
            `failed_job_ids` longtext NOT NULL,
            `options` mediumtext DEFAULT NULL,
            `cancelled_at` int(11) DEFAULT NULL,
            `created_at` int(11) NOT NULL,
            `finished_at` int(11) DEFAULT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
    'failed_jobs' => "
        CREATE TABLE IF NOT EXISTS `failed_jobs` (
            `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            `uuid` varchar(255) NOT NULL,
            `connection` text NOT NULL,
            `queue` text NOT NULL,
            `payload` longtext NOT NULL,
            `exception` longtext NOT NULL,
            `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ",
];

$missingTables = [];
$existingTables = [];
$createdTables = [];
$failedTables = [];

echo "Checking existing tables...\n\n";

// Check which tables exist
foreach ($systemTables as $tableName => $sql) {
    try {
        $exists = Schema::hasTable($tableName);
        if ($exists) {
            $existingTables[] = $tableName;
            echo "✓ {$tableName} - EXISTS\n";
        } else {
            $missingTables[] = $tableName;
            echo "✗ {$tableName} - MISSING\n";
        }
    } catch (\Exception $e) {
        echo "⚠ {$tableName} - ERROR: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Summary ===\n";
echo "Existing tables: " . count($existingTables) . "\n";
echo "Missing tables: " . count($missingTables) . "\n\n";

if (empty($missingTables)) {
    echo "✅ All Laravel system tables exist!\n";
    exit(0);
}

echo "=== Creating Missing Tables ===\n\n";

// Create missing tables
foreach ($missingTables as $tableName) {
    if (!isset($systemTables[$tableName])) {
        continue;
    }

    try {
        echo "Creating {$tableName}...\n";
        DB::statement($systemTables[$tableName]);
        $createdTables[] = $tableName;
        echo "✅ {$tableName} created successfully\n\n";
    } catch (\Exception $e) {
        $failedTables[] = $tableName;
        echo "❌ Failed to create {$tableName}: " . $e->getMessage() . "\n\n";
    }
}

echo "\n=== Final Summary ===\n";
echo "Database: {$database}\n";
echo "Existing tables: " . count($existingTables) . " (" . implode(', ', $existingTables) . ")\n";
echo "Created tables: " . count($createdTables) . " (" . implode(', ', $createdTables) . ")\n";

if (!empty($failedTables)) {
    echo "Failed tables: " . count($failedTables) . " (" . implode(', ', $failedTables) . ")\n";
}

if (empty($failedTables)) {
    echo "\n✅ All Laravel system tables are ready!\n";
    echo "\nYou can now use the CI database directly.\n";
    echo "Make sure your .env file points to the correct database:\n";
    echo "  - Port 8000: DB_DATABASE=rullart_kuwaitalpha\n";
    echo "  - Port 8001: DB_DATABASE=rullart_qataralpha\n";
} else {
    echo "\n⚠ Some tables failed to create. Please check the errors above.\n";
    exit(1);
}
