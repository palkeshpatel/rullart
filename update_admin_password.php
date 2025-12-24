<?php

/**
 * Update Admin Password for Qatar Database
 * Usage: php update_admin_password.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Update Admin Password ===\n\n";

// Update password for info@rullart.com
$username = 'info@rullart.com';
$password = 'rullart@2025';
$passwordHash = md5($password);

echo "Username: {$username}\n";
echo "New Password: {$password}\n";
echo "Password Hash: {$passwordHash}\n\n";

try {
    // First, check all admin users
    echo "Checking all admin users in database...\n";
    $allAdmins = DB::table('admin')->get();
    echo "Found " . count($allAdmins) . " admin user(s):\n";
    foreach ($allAdmins as $adm) {
        echo "  - ID: {$adm->id}, User: '{$adm->user}', Email: '{$adm->email}'\n";
    }
    echo "\n";

    // Check if admin exists by username or email
    $admin = DB::table('admin')
        ->where('user', $username)
        ->orWhere('email', $username)
        ->first();

    if ($admin) {
        echo "✓ Admin user found (ID: {$admin->id})\n";
        echo "  Username: {$admin->user}\n";
        echo "  Email: {$admin->email}\n";
        echo "  Current password hash: {$admin->pass}\n\n";

        // Update password
        DB::table('admin')->where('id', $admin->id)->update([
            'pass' => $passwordHash,
        ]);

        echo "✅ Password updated successfully!\n\n";

        // Verify
        $updatedAdmin = DB::table('admin')->where('id', $admin->id)->first();
        echo "Verification:\n";
        echo "  Username: {$updatedAdmin->user}\n";
        echo "  Email: {$updatedAdmin->email}\n";
        echo "  New Password Hash: {$updatedAdmin->pass}\n";
        echo "  Expected Hash: {$passwordHash}\n";

        if ($updatedAdmin->pass === $passwordHash) {
            echo "\n✅ Password hash matches! You can now login.\n";
        } else {
            echo "\n❌ Password hash mismatch! Please check again.\n";
        }
    } else {
        echo "❌ Admin user not found with username/email: {$username}\n";
        echo "Updating first admin user instead...\n\n";

        // Update the first admin user
        $firstAdmin = DB::table('admin')->first();
        if ($firstAdmin) {
            DB::table('admin')->where('id', $firstAdmin->id)->update([
                'user' => $username,
                'pass' => $passwordHash,
                'email' => $username,
            ]);
            echo "✅ Updated admin user ID {$firstAdmin->id} with new credentials!\n";
        } else {
            echo "❌ No admin users found in database!\n";
            exit(1);
        }
    }

    echo "\n=== Login Credentials ===\n";
    echo "Username: {$username}\n";
    echo "Password: {$password}\n";
    echo "\n✅ You can now login with these credentials!\n";
} catch (\Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}