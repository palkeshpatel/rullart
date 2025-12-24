<?php

/**
 * Fix Admin Username - Update admin user to use info@rullart.com
 * Usage: php fix_admin_username.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Fix Admin Username ===\n\n";

$targetUsername = 'info@rullart.com';
$targetPassword = 'rullart@2025';
$passwordHash = md5($targetPassword);

try {
    // Show all admin users
    echo "Current admin users in database:\n";
    $allAdmins = DB::table('admin')->get();
    foreach ($allAdmins as $admin) {
        echo "  ID: {$admin->id}, Username: '{$admin->user}', Email: '{$admin->email}'\n";
    }
    echo "\n";
    
    // Check if target username already exists
    $existingAdmin = DB::table('admin')->where('user', $targetUsername)->first();
    
    if ($existingAdmin) {
        echo "✓ Admin with username '{$targetUsername}' already exists (ID: {$existingAdmin->id})\n";
        echo "Updating password...\n\n";
        
        DB::table('admin')->where('id', $existingAdmin->id)->update([
            'pass' => $passwordHash,
        ]);
        
        echo "✅ Password updated!\n";
    } else {
        // Update the first admin user (usually ID 1) to use the target username
        echo "Admin with username '{$targetUsername}' not found.\n";
        echo "Updating first admin user (ID 1) to use this username...\n\n";
        
        $firstAdmin = DB::table('admin')->where('id', 1)->first();
        
        if ($firstAdmin) {
            DB::table('admin')->where('id', 1)->update([
                'user' => $targetUsername,
                'email' => $targetUsername,
                'pass' => $passwordHash,
            ]);
            
            echo "✅ Updated admin user ID 1:\n";
            echo "  Username: {$targetUsername}\n";
            echo "  Email: {$targetUsername}\n";
            echo "  Password: {$targetPassword}\n";
        } else {
            echo "❌ No admin user with ID 1 found!\n";
            echo "Creating new admin user...\n\n";
            
            DB::table('admin')->insert([
                'id' => 1,
                'user' => $targetUsername,
                'pass' => $passwordHash,
                'name' => 'Rullart',
                'email' => $targetUsername,
                'site' => 1,
                'user_role' => 1,
                'lock_access' => 0,
                'fkstoreid' => 1,
                'created_date' => now(),
            ]);
            
            echo "✅ Created new admin user!\n";
        }
    }
    
    // Verify
    $updatedAdmin = DB::table('admin')->where('user', $targetUsername)->first();
    
    if ($updatedAdmin) {
        echo "\n=== Verification ===\n";
        echo "Username: {$updatedAdmin->user}\n";
        echo "Email: {$updatedAdmin->email}\n";
        echo "Password Hash: {$updatedAdmin->pass}\n";
        echo "Expected Hash: {$passwordHash}\n";
        
        if ($updatedAdmin->pass === $passwordHash) {
            echo "\n✅ Password hash matches!\n";
        } else {
            echo "\n❌ Password hash mismatch!\n";
        }
        
        echo "\n=== Login Credentials ===\n";
        echo "Username: {$targetUsername}\n";
        echo "Password: {$targetPassword}\n";
        echo "\n✅ You can now login with these credentials!\n";
    } else {
        echo "\n❌ Failed to verify admin user!\n";
    }
    
} catch (\Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

