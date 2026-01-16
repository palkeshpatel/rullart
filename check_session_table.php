<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

echo "=== SESSION CONNECTION CHECK ===\n\n";

// Check session configuration
echo "Session Driver: " . config('session.driver') . "\n";
echo "Session Connection: " . config('session.connection') . "\n\n";

// Check database configurations
echo "=== DATABASE CONFIGURATIONS ===\n";
echo "MySQL Connection DB: " . config('database.connections.mysql.database') . "\n";
echo "Session Connection DB: " . config('database.connections.session.database') . "\n\n";

// Check if sessions table exists in session connection
echo "=== CHECKING SESSIONS TABLE ===\n";
try {
    $exists = DB::connection('session')->getSchemaBuilder()->hasTable('sessions');
    echo "Sessions table exists in 'session' connection: " . ($exists ? "YES ✓" : "NO ✗") . "\n";
    
    if ($exists) {
        $count = DB::connection('session')->table('sessions')->count();
        echo "Number of sessions in table: {$count}\n";
        
        // Show sample session
        if ($count > 0) {
            $session = DB::connection('session')->table('sessions')->first();
            echo "Sample session ID: {$session->id}\n";
            echo "Session payload length: " . strlen($session->payload) . " bytes\n";
        }
    }
} catch (\Exception $e) {
    echo "Error checking session connection: " . $e->getMessage() . "\n";
}

// Check if sessions table exists in mysql connection
try {
    $exists = DB::connection('mysql')->getSchemaBuilder()->hasTable('sessions');
    echo "Sessions table exists in 'mysql' connection: " . ($exists ? "YES ✓" : "NO ✗") . "\n";
    
    if ($exists) {
        $count = DB::connection('mysql')->table('sessions')->count();
        echo "Number of sessions in mysql connection: {$count}\n";
    }
} catch (\Exception $e) {
    echo "Error checking mysql connection: " . $e->getMessage() . "\n";
}

echo "\n=== CHECKING ACTIVE SESSION ===\n";
if (app()->runningInConsole()) {
    echo "Running in console - cannot access HTTP session\n";
} else {
    try {
        $session = session();
        if ($session) {
            echo "Session ID: " . $session->getId() . "\n";
            echo "Session has shoppingcartid: " . ($session->has('shoppingcartid') ? "YES" : "NO") . "\n";
            if ($session->has('shoppingcartid')) {
                echo "Shopping Cart ID: " . $session->get('shoppingcartid') . "\n";
            }
        }
    } catch (\Exception $e) {
        echo "Error accessing session: " . $e->getMessage() . "\n";
    }
}

echo "\n=== DONE ===\n";
