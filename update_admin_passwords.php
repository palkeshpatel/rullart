<?php

/**
 * Script to update all admin passwords to "password" (MD5)
 * Run this once: php update_admin_passwords.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Update all admin passwords to MD5 hash of "password"
    $passwordHash = md5('password');
    
    $updated = DB::table('admin')->update(['pass' => $passwordHash]);
    
    echo "Successfully updated {$updated} admin password(s) to 'password' (MD5: {$passwordHash})\n";
    echo "You can now login with any username from admin table and password: password\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Make sure your database connection is configured correctly in .env file\n";
}

