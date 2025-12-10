<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    // Drop if exists
    Schema::dropIfExists('returnrequest');
    
    // Create the table
    DB::statement("
        CREATE TABLE `returnrequest` (
            `requestid` int(11) NOT NULL,
            `firstname` varchar(50) DEFAULT NULL,
            `lastname` varchar(50) DEFAULT NULL,
            `orderno` varchar(20) DEFAULT NULL,
            `email` varchar(80) DEFAULT NULL,
            `mobile` varchar(50) DEFAULT NULL,
            `reason` varchar(5000) DEFAULT NULL,
            `submiton` datetime DEFAULT NULL,
            `lang` varchar(5) DEFAULT NULL,
            PRIMARY KEY (`requestid`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci
    ");
    
    echo "✅ Table 'returnrequest' created successfully!\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

