<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // CI Database configuration
    $ciDb = new PDO(
        "mysql:host=127.0.0.1;dbname=rullart_rullart;charset=utf8mb4",
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    echo "Copying returnrequest data from CI database...\n";
    
    // Get data from CI database
    $data = $ciDb->query("SELECT * FROM returnrequest")->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($data)) {
        echo "No data to copy.\n";
        exit(0);
    }
    
    echo "Found " . count($data) . " records to copy.\n";
    
    // Insert into Laravel database
    foreach ($data as $row) {
        DB::table('returnrequest')->insert($row);
    }
    
    echo "✅ Successfully copied " . count($data) . " records to returnrequest table!\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

