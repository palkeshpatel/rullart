# Laravel Admin Panel Migration Guide

## Overview
This Laravel project is a migration from the old CodeIgniter (CI) admin panel. All database tables, data, and functionality have been migrated to Laravel while maintaining compatibility with the existing CI database structure.

## Project Structure

```
design-rullart/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Admin/
│   │   │       ├── AdminLoginController.php    # Admin authentication
│   │   │       ├── DashboardController.php     # Dashboard statistics
│   │   │       ├── CustomerController.php      # Customer management
│   │   │       └── OrderController.php          # Order management
│   │   └── Middleware/
│   │       └── Admin.php                        # Admin route protection
│   └── Models/
│       ├── Admin.php                           # Admin model (custom auth)
│       ├── Customer.php                        # Customer model
│       ├── Order.php                           # Order model
│       ├── Product.php                         # Product model
│       ├── Category.php                        # Category model
│       ├── OrderItem.php                       # Order items model
│       ├── ProductRating.php                   # Product ratings model
│       └── ReturnRequest.php                   # Return requests model
├── database/
│   ├── migrations/                             # All table migrations
│   └── seeders/
│       └── AdminSeeder.php                     # Admin user seeder
├── resources/
│   └── views/
│       ├── admin/                              # Admin panel views
│       │   ├── login.blade.php                 # Admin login page
│       │   ├── dashboard.blade.php             # Dashboard view
│       │   ├── customers.blade.php             # Customer listing
│       │   └── orders.blade.php                # Order listing
│       └── layouts/
│           └── partials/
│               └── sidenav.blade.php           # Sidebar menu
├── routes/
│   └── admin.php                               # Admin routes
└── config/
    └── auth.php                                # Authentication config (admin guard)

```

## Database Configuration

### Current Setup
- **Laravel Database:** `laravel_123`
- **CI Database (Source):** `rullart_rullart`
- **Connection:** Both databases are on the same MySQL server (localhost)

### Database Tables
All 66 tables from the CI project have been migrated:
- Core tables: `customers`, `ordermaster`, `orderitems`, `products`, `category`
- Admin tables: `admin`
- Support tables: `returnrequest`, `productrating`, `shoppingcart`, etc.

## Initial Setup

### 1. Install Dependencies
```bash
cd design-rullart
composer install
npm install
```

### 2. Configure Environment
Copy `.env.example` to `.env` and update:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_123
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Generate Application Key
```bash
php artisan key:generate
```

### 4. Run Migrations
```bash
php artisan migrate
```

### 5. Create Admin User
```bash
php artisan db:seed --class=AdminSeeder
```

Or use the password fix script:
```bash
php fix_admin_password.php
```

### 6. Copy Data from CI Database (Optional)
If you need to copy fresh data from CI database:
```bash
php copy_data_from_ci.php
```

## Admin Login

### Default Credentials
- **Username:** `admin`
- **Password:** `password`

### Custom Admin User
To create/update admin user with custom credentials:
```bash
php fix_admin_password.php
```

Edit the script to change username/password, or run:
```bash
php artisan tinker
```
Then:
```php
DB::table('admin')->insert([
    'id' => 1,
    'user' => 'your_username',
    'pass' => md5('your_password'),
    'name' => 'Your Name',
    'email' => 'your@email.com',
    'site' => 0,
    'user_role' => 1,
    'lock_access' => 0,
    'fkstoreid' => 1,
    'created_date' => now(),
]);
```

## Authentication System

### Admin Authentication
- Uses custom `Admin` model with MD5 password hashing
- Custom guard: `auth:admin`
- Login field: `user` (username/email)
- Password field: `pass` (MD5 hash)

### Login Flow
1. User submits credentials at `/admin/login`
2. `AdminLoginController@store` validates and authenticates
3. Uses MD5 hash comparison: `md5($password) === $admin->pass`
4. Session stores admin's integer `id` (not username)

## Common Tasks

### Fix Missing Tables
If you encounter "table doesn't exist" errors:
```bash
php fix_all_missing_tables.php
```

This script will:
- Check all core tables
- Create missing tables from CI database structure
- Copy data from CI database
- Handle invalid datetime values

### Update Admin Password
```bash
php fix_admin_password.php
```

Or manually:
```bash
php artisan tinker
```
```php
DB::table('admin')->where('user', 'info@rullart.com')->update(['pass' => md5('new_password')]);
```

### Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

### Check Migration Status
```bash
php artisan migrate:status
```

## Development Workflow

### Adding New Admin Pages

1. **Create Controller:**
```bash
php artisan make:controller Admin/YourController
```

2. **Add Route** in `routes/admin.php`:
```php
Route::get('your-page', [YourController::class, 'index'])->name('your-page');
```

3. **Create View** in `resources/views/admin/your-page.blade.php`

4. **Add Menu Item** in `resources/views/layouts/partials/sidenav.blade.php`:
```blade
<li class="side-nav-item">
    <a href="{{ route('admin.your-page') }}" class="side-nav-link">
        <span class="menu-icon"><i class="ti ti-icon-name"></i></span>
        <span class="menu-text">Your Page</span>
    </a>
</li>
```

### Working with Models

All models use the existing CI table structure:

```php
use App\Models\Customer;

// Get customers
$customers = Customer::orderBy('createdon', 'desc')->get();

// Get orders with customer
$orders = Order::with('customer')->get();

// Note: Primary keys are NOT auto-incrementing
// Use integer IDs from CI database
```

### Database Queries

Since we're using the CI database structure, be aware:
- Primary keys are integers (not auto-increment in some cases)
- Timestamps use custom fields (`createdon`, `updateddate`, etc.)
- Some tables don't have `created_at`/`updated_at` columns

Example:
```php
// Correct
Customer::where('customerid', 123)->first();

// Wrong (if table doesn't have timestamps)
Customer::where('created_at', '>', now())->get();
```

## Troubleshooting

### Error: "Table doesn't exist"
**Solution:** Run `php fix_all_missing_tables.php`

### Error: "Invalid datetime format: 0000-00-00"
**Solution:** The fix script handles this automatically. If you see this error, the data copy failed. Re-run the fix script.

### Error: "These credentials do not match"
**Solution:** 
1. Check if admin user exists: `php artisan tinker` then `DB::table('admin')->get()`
2. Update password: `php fix_admin_password.php`
3. Verify MD5 hash matches

### Error: "Incorrect integer value for user_id"
**Solution:** This was fixed in `Admin.php` model. The `getAuthIdentifier()` now returns integer `id` instead of username string.

### Session Issues
Clear all caches:
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

## Important Files

### Key Configuration Files
- `config/auth.php` - Admin guard configuration
- `.env` - Database connection settings

### Key Models
- `app/Models/Admin.php` - Admin authentication model
- `app/Models/Customer.php` - Customer model
- `app/Models/Order.php` - Order model

### Key Controllers
- `app/Http/Controllers/Admin/AdminLoginController.php` - Login/logout
- `app/Http/Controllers/Admin/DashboardController.php` - Dashboard data

### Key Views
- `resources/views/admin/login.blade.php` - Login form
- `resources/views/admin/dashboard.blade.php` - Dashboard
- `resources/views/layouts/partials/sidenav.blade.php` - Sidebar menu

## Helper Scripts

### Available Scripts
1. **`fix_all_missing_tables.php`** - Fixes all missing tables and copies data
2. **`fix_admin_password.php`** - Creates/updates admin user password
3. **`copy_data_from_ci.php`** - Copies all data from CI database
4. **`update_admin_passwords.php`** - Updates all admin passwords to "password"
5. **`generate_migrations.php`** - Generates migrations from SQL (already used)

## Best Practices

1. **Always use Eloquent models** instead of raw DB queries when possible
2. **Check table existence** before running queries in new code
3. **Handle NULL values** for datetime fields (CI database has many `0000-00-00` dates)
4. **Use relationships** defined in models (e.g., `Order::with('customer')`)
5. **Test admin login** after any authentication changes

## Support

If you encounter issues:
1. Check this guide first
2. Run `php fix_all_missing_tables.php` to ensure all tables exist
3. Clear all caches
4. Check Laravel logs: `storage/logs/laravel.log`
5. Verify database connection in `.env`

## Next Steps

- [ ] Complete all admin page views
- [ ] Add CRUD operations for all entities
- [ ] Implement search and filtering
- [ ] Add export functionality
- [ ] Set up proper error handling
- [ ] Add unit tests

---

**Last Updated:** December 2025
**Version:** 1.0

