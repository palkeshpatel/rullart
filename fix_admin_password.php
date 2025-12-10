<?php

/**
 * Script to fix admin password for info@rullart.com
 * Usage: php fix_admin_password.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $username = 'info@rullart.com';
    $password = 'rullart@2025';
    $passwordHash = md5($password);
    
    // Check if admin exists
    $admin = DB::table('admin')->where('user', $username)->first();
    
    if ($admin) {
        // Update password
        DB::table('admin')->where('user', $username)->update(['pass' => $passwordHash]);
        echo "✅ Password updated for: {$username}\n";
        echo "   Password: {$password}\n";
        echo "   MD5 Hash: {$passwordHash}\n";
    } else {
        // Create admin user
        $maxId = DB::table('admin')->max('id') ?? 0;
        $newId = $maxId + 1;
        
        DB::table('admin')->insert([
            'id' => $newId,
            'user' => $username,
            'pass' => $passwordHash,
            'name' => 'Info Admin',
            'email' => $username,
            'site' => 1,
            'user_role' => 1,
            'lock_access' => 0,
            'fkstoreid' => 1,
            'created_date' => now(),
        ]);
        
        echo "✅ Admin user created: {$username}\n";
        echo "   Password: {$password}\n";
        echo "   MD5 Hash: {$passwordHash}\n";
    }
    
    echo "\nYou can now login with:\n";
    echo "   Username: {$username}\n";
    echo "   Password: {$password}\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

