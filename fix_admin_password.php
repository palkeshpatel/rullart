<?php

/**
 * Fix Admin Password - Copy password from CI database or set a new one
 * Usage: php fix_admin_password.php [username] [password]
 *
 * If no arguments provided, it will:
 * 1. Check if admin exists in Laravel database
 * 2. Try to copy password from CI database
 * 3. Or create a default admin user
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Get username and password from command line arguments or use defaults
$username = $argv[1] ?? 'info@rullart.com';
$password = $argv[2] ?? null;

// CI Database Configuration (from CI project)
$ciConfig = [
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'rullart_rullart', // Default, can be changed
    'username' => 'root',
    'password' => '',
];

echo "=== Fix Admin Password Tool ===\n\n";
echo "Username: {$username}\n";

// Check which CI database to use based on Laravel DB
$laravelDb = env('DB_DATABASE', 'rullart_kuwaitbeta_laravel');
if (strpos($laravelDb, 'qatarbeta') !== false) {
    $ciConfig['database'] = 'rullart_rullart_qatarbeta';
    echo "Using Qatar CI database: {$ciConfig['database']}\n";
} else {
    echo "Using Kuwait CI database: {$ciConfig['database']}\n";
}

try {
    // Check if admin exists in Laravel
    $admin = DB::table('admin')->where('user', $username)->first();

    if ($admin) {
        echo "✓ Admin user found in Laravel database\n";
        echo "  Current password hash: " . substr($admin->pass, 0, 10) . "...\n\n";
    } else {
        echo "⚠ Admin user NOT found in Laravel database\n\n";
    }

    // Try to get password from CI database
    try {
        $ciConnection = new PDO(
            "mysql:host={$ciConfig['host']};port={$ciConfig['port']};dbname={$ciConfig['database']}",
            $ciConfig['username'],
            $ciConfig['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $ciAdmin = $ciConnection->prepare("SELECT * FROM admin WHERE user = ?");
        $ciAdmin->execute([$username]);
        $ciAdminData = $ciAdmin->fetch(PDO::FETCH_ASSOC);

        if ($ciAdminData) {
            echo "✓ Found admin in CI database\n";
            echo "  CI Password hash: " . substr($ciAdminData['pass'], 0, 10) . "...\n\n";

            // Copy from CI database
            if ($admin) {
                // Update existing admin
                DB::table('admin')->where('user', $username)->update([
                    'pass' => $ciAdminData['pass'],
                ]);
                echo "✅ Updated admin password from CI database\n";
            } else {
                // Create new admin with CI data
                $adminData = $ciAdminData;
                // Remove 'pass' temporarily, then add it back after processing
                $passHash = $adminData['pass'];
                unset($adminData['pass']);

                // Handle any datetime fields that might be invalid
                foreach ($adminData as $key => $value) {
                    if (is_string($value) && (
                        $value === '0000-00-00 00:00:00' ||
                        $value === '0000-00-00' ||
                        preg_match('/^0000-\d{2}-\d{2}/', $value)
                    )) {
                        $adminData[$key] = null;
                    }
                }

                $adminData['pass'] = $passHash;
                DB::table('admin')->insert($adminData);
                echo "✅ Created admin user from CI database\n";
            }
        } else {
            echo "⚠ Admin NOT found in CI database\n";

            // Create or update with provided/new password
            if ($password) {
                $passwordHash = md5($password);
                echo "Using provided password\n";
            } else {
                $passwordHash = md5('password');
                echo "Using default password: 'password'\n";
            }

            if ($admin) {
                DB::table('admin')->where('user', $username)->update([
                    'pass' => $passwordHash,
                ]);
                echo "✅ Updated admin password\n";
            } else {
                // Create default admin
                DB::table('admin')->insert([
                    'id' => 1,
                    'user' => $username,
                    'pass' => $passwordHash,
                    'name' => 'Administrator',
                    'email' => $username,
                    'site' => 0,
                    'user_role' => 1,
                    'lock_access' => 0,
                    'fkstoreid' => 1,
                    'created_date' => now(),
                ]);
                echo "✅ Created admin user with default password\n";
            }
        }
    } catch (PDOException $e) {
        echo "⚠ Could not connect to CI database: " . $e->getMessage() . "\n";
        echo "Creating/updating with default password...\n\n";

        $passwordHash = $password ? md5($password) : md5('password');

        if ($admin) {
            DB::table('admin')->where('user', $username)->update([
                'pass' => $passwordHash,
            ]);
            echo "✅ Updated admin password\n";
        } else {
            DB::table('admin')->insert([
                'id' => 1,
                'user' => $username,
                'pass' => $passwordHash,
                'name' => 'Administrator',
                'email' => $username,
                'site' => 0,
                'user_role' => 1,
                'lock_access' => 0,
                'fkstoreid' => 1,
                'created_date' => now(),
            ]);
            echo "✅ Created admin user\n";
        }
    }

    // Show final admin info
    $finalAdmin = DB::table('admin')->where('user', $username)->first();
    echo "\n=== Final Admin Info ===\n";
    echo "Username: {$finalAdmin->user}\n";
    echo "Email: {$finalAdmin->email}\n";
    echo "Password Hash: " . substr($finalAdmin->pass, 0, 20) . "...\n";

    if (!$password && !isset($ciAdminData)) {
        echo "\n⚠ Default password is 'password'\n";
        echo "To set a custom password, run:\n";
        echo "  php fix_admin_password.php {$username} your_new_password\n";
    }

    echo "\n✅ Done! You can now login with:\n";
    echo "  Username: {$username}\n";
    if ($password) {
        echo "  Password: (the password you provided)\n";
    } elseif (isset($ciAdminData)) {
        echo "  Password: (same as CI database)\n";
    } else {
        echo "  Password: password\n";
    }
} catch (\Exception $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}