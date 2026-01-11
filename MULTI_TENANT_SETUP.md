# Multi-Tenant Database Switching Setup Guide

This Laravel application supports multi-tenant database switching based on domain (production) or port (local development). The same codebase serves multiple tenants with different databases.

## 📋 Overview

-   **Kuwait Tenant**: Database `rullart_kuwaitalpha`
-   **Qatar Tenant**: Database `rullart_qataralpha`

### Production Domains

-   `https://betakuwait.techiebrothers.in` → Kuwait database
-   `https://betaqatar.techiebrothers.in` → Qatar database

### Local Development

-   `http://localhost:8000` → Kuwait database
-   `http://localhost:9000` → Qatar database

---

## 🚀 Setup Instructions

### Step 1: Configure `.env` File

#### Local Development (.env)

```env
APP_ENV=local
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USERNAME=root
DB_PASSWORD=
DB_DATABASE=dummy_db  # This will be overridden by domain switching
```

#### Production (.env)

```env
APP_ENV=production
APP_DEBUG=false

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_USERNAME=rullart_user
DB_PASSWORD=strong_password
DB_DATABASE=dummy_db  # This will be overridden by domain switching
```

**Note**: The `DB_DATABASE` in `.env` is used as a fallback/default. The actual database is determined automatically based on the domain/port.

---

### Step 2: Database Mapping Configuration

The mapping is configured in `config/domain_db.php`:

```php
return [
    'local' => [
        '8000' => 'rullart_kuwaitalpha',  // Kuwait
        '9000' => 'rullart_qataralpha',   // Qatar
    ],
    'production' => [
        'betakuwait.techiebrothers.in' => 'rullart_kuwaitalpha',
        'betaqatar.techiebrothers.in'  => 'rullart_qataralpha',
    ],
    'default' => env('DB_DATABASE', 'rullart_kuwaitalpha'),
    'enabled' => env('DB_SWITCHING_ENABLED', true),
];
```

**To add a new tenant:**

1. Add the mapping to `config/domain_db.php`
2. Ensure the database exists
3. Run migrations on the new database

---

### Step 3: How It Works

The database switching logic is implemented in `app/Providers/AppServiceProvider.php`:

1. **Automatic Detection**: On each HTTP request, the system:

    - Checks the environment (local/production)
    - Gets the port (local) or domain (production)
    - Looks up the corresponding database from the mapping
    - Switches the database connection automatically

2. **Console Commands**: Artisan commands (migrations, seeders, etc.) use the default `.env` database. This allows you to:

    - Run migrations on specific databases
    - Use `--database` flag if needed
    - Work with the default database for maintenance tasks

3. **Error Handling**:
    - If no mapping is found, uses the default database
    - In production, aborts with 403 if tenant not configured (security)
    - Logs all database switches for debugging

---

### Step 4: Running Local Development Servers

Open **two terminal windows**:

#### Terminal 1 - Kuwait (Port 8000)

```bash
php artisan serve --port=8000
```

Access at: `http://localhost:8000`

#### Terminal 2 - Qatar (Port 9000)

```bash
php artisan serve --port=9000
```

Access at: `http://localhost:9000`

---

## 🛠️ Usage Examples

### Using TenantHelper in Your Code

The `App\Helpers\TenantHelper` class provides useful methods:

```php
use App\Helpers\TenantHelper;

// Get current database name
$db = TenantHelper::getCurrentDatabase();

// Get tenant name (Kuwait/Qatar)
$tenant = TenantHelper::getTenantName();

// Check if current tenant is Kuwait
if (TenantHelper::isKuwait()) {
    // Kuwait-specific logic
}

// Check if current tenant is Qatar
if (TenantHelper::isQatar()) {
    // Qatar-specific logic
}

// Get all tenant information
$info = TenantHelper::getTenantInfo();
// Returns: ['database', 'tenant_key', 'tenant_name', 'is_kuwait', 'is_qatar', 'environment']
```

### In Controllers

```php
use App\Helpers\TenantHelper;

class ProductController extends Controller
{
    public function index()
    {
        $tenant = TenantHelper::getTenantName();
        $products = Product::all();

        return view('products.index', [
            'products' => $products,
            'tenant' => $tenant,
        ]);
    }
}
```

### In Blade Templates

```blade
@if(\App\Helpers\TenantHelper::isKuwait())
    <h1>Kuwait Store</h1>
@elseif(\App\Helpers\TenantHelper::isQatar())
    <h1>Qatar Store</h1>
@endif

<p>Current Database: {{ \App\Helpers\TenantHelper::getCurrentDatabase() }}</p>
```

---

## 🔧 Maintenance & Troubleshooting

### Disable Database Switching

To temporarily disable automatic database switching (use `.env` database only):

```env
DB_SWITCHING_ENABLED=false
```

### Running Migrations

Migrations run on the default `.env` database. To run on specific databases:

```bash
# Set DB_DATABASE in .env temporarily
# Or use database connection parameter
php artisan migrate --database=mysql
```

### Running Seeders

```bash
# Seeders use the current database connection
php artisan db:seed
```

### Debugging

Database switches are logged when:

-   `APP_ENV=local` OR
-   `APP_DEBUG=true`

Check logs in `storage/logs/laravel.log` for entries like:

```
Database Switch [switched]: Environment=local, Database=rullart_kuwaitalpha, Key=8000
```

### Common Issues

1. **"Tenant not configured" error in production**

    - Check that the domain is added to `config/domain_db.php`
    - Verify the domain matches exactly (case-sensitive)

2. **Wrong database being used**

    - Check `config/domain_db.php` mapping
    - Verify port/domain is correct
    - Check logs for database switch information

3. **Artisan commands using wrong database**
    - Artisan commands use `.env` database by default
    - This is intentional - use `--database` flag if needed

---

## 📁 File Structure

```
rullart/
├── config/
│   └── domain_db.php          # Domain/Port to DB mapping
├── app/
│   ├── Providers/
│   │   └── AppServiceProvider.php  # Database switching logic
│   └── Helpers/
│       └── TenantHelper.php        # Helper methods
└── .env                            # Database configuration
```

---

## ✅ Testing

### Test Local Setup

1. Start Kuwait server: `php artisan serve --port=8000`
2. Visit `http://localhost:8000` and verify it uses Kuwait database
3. Start Qatar server: `php artisan serve --port=9000`
4. Visit `http://localhost:9000` and verify it uses Qatar database

### Verify Database Switching

Add this to any controller temporarily:

```php
dd([
    'database' => \App\Helpers\TenantHelper::getCurrentDatabase(),
    'tenant' => \App\Helpers\TenantHelper::getTenantName(),
    'info' => \App\Helpers\TenantHelper::getTenantInfo(),
]);
```

---

## 🔒 Security Notes

1. **Production**: Always set `APP_ENV=production` and `APP_DEBUG=false`
2. **Database Access**: Ensure proper database user permissions
3. **Tenant Isolation**: Each tenant should only access their own database
4. **Error Messages**: Don't expose database names in production error messages

---

## 📝 Notes

-   Database switching happens automatically on every HTTP request
-   Console commands (artisan) use the default `.env` database
-   The system is environment-aware (local vs production)
-   All database switches are logged for debugging
-   The system gracefully falls back to default database if mapping not found

---

## 🎯 Summary

This multi-tenant setup allows you to:

-   ✅ Use the same codebase for multiple tenants
-   ✅ Automatically switch databases based on domain/port
-   ✅ Develop locally using different ports
-   ✅ Deploy to production with different domains
-   ✅ Maintain separate databases for each tenant
-   ✅ Use helper methods to get tenant information

For questions or issues, check the logs or review the implementation in `AppServiceProvider.php`.
