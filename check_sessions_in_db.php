<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== CHECKING SESSIONS IN DATABASE ===\n\n";

try {
    $sessions = DB::connection('session')
        ->table('sessions')
        ->select('id', 'last_activity', DB::raw('LENGTH(payload) as payload_size'))
        ->orderBy('last_activity', 'desc')
        ->limit(10)
        ->get();
    
    echo "Recent sessions in database: " . DB::connection('session')->table('sessions')->count() . "\n\n";
    
    foreach ($sessions as $session) {
        $sessionIdShort = substr($session->id, 0, 40) . '...';
        $lastActivity = date('Y-m-d H:i:s', $session->last_activity);
        echo "Session ID: {$sessionIdShort}\n";
        echo "  Last Activity: {$lastActivity}\n";
        echo "  Payload Size: {$session->payload_size} bytes\n\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "=== DONE ===\n";
