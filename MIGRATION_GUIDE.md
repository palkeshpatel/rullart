# Complete Database Migration Guide: CodeIgniter to Laravel

## Overview

This Laravel project is a migration from CodeIgniter (CI) admin panel. The project supports **multiple database instances** running on different ports, each with its own CI source database and Laravel target database.

### Database Configuration

The project supports two database instances:

| Instance | CI Source Database          | Laravel Target Database      | Laravel Server Port | Description     |
| -------- | --------------------------- | ---------------------------- | ------------------- | --------------- |
| 1        | `rullart_rullart`           | `rullart_kuwaitbeta_laravel` | 8000                | Kuwait Database |
| 2        | `rullart_rullart_qatarbeta` | `rullart_qatarbeta_laravel`  | 8001                | Qatar Database  |

**Important Notes**:

-   **Laravel Server Ports**: Ports 8000 and 8001 are for running Laravel development server (`php artisan serve`), NOT MySQL ports
-   **MySQL Port**: All database connections use MySQL port `3306` (default MySQL port)
-   Both CI and Laravel databases must exist on the same MySQL server before migration

---

## Table of Contents

1. [Prerequisites](#prerequisites)
2. [Initial Setup](#initial-setup)
3. [Migration Process](#migration-process)
4. [Step-by-Step Migration](#step-by-step-migration)
5. [Multi-Database Setup](#multi-database-setup)
6. [Table & Column Validation](#table--column-validation)
7. [Troubleshooting](#troubleshooting)
8. [Best Practices](#best-practices)

---

## Prerequisites

### Required Software

-   PHP >= 8.2
-   Composer
-   MySQL/MariaDB
-   Node.js & NPM (for frontend assets)

### Database Requirements

1. **CI Source Databases** (must exist):

    - `rullart_rullart` (Kuwait)
    - `rullart_rullart_qatarbeta` (Qatar)

2. **Laravel Target Databases** (will be created/used):

    - `rullart_kuwaitbeta_laravel` (Kuwait)
    - `rullart_qatarbeta_laravel` (Qatar)

3. **Database Access**:
    - Host: `127.0.0.1`
    - Port: `3306`
    - Username: `root` (or your MySQL username)
    - Password: (your MySQL password)

---

## Initial Setup

### Step 1: Install Dependencies

```bash
cd rullart
composer install
npm install
```

### Step 2: Configure Environment

Create `.env` file or copy from `.env.example`:

```env
APP_NAME="Rullart Admin"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rullart_kuwaitbeta_laravel
DB_USERNAME=root
DB_PASSWORD=
```

**Note**: For Qatar instance, use `DB_DATABASE=rullart_qatarbeta_laravel` and run Laravel server on port 8001 (`php artisan serve --port=8001`).

### Step 3: Generate Application Key

```bash
php artisan key:generate
```

### Step 4: Create Target Databases

Create the Laravel target databases in MySQL:

```sql
CREATE DATABASE IF NOT EXISTS rullart_kuwaitbeta_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE IF NOT EXISTS rullart_qatarbeta_laravel CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## Migration Process

### Overview

The migration process consists of three main steps:

1. **Create Table Structure** - Run Laravel migrations to create tables
2. **Validate Structure** - Check table/column matching between CI and Laravel
3. **Migrate Data** - Copy data from CI database to Laravel database

### Migration Methods

You can migrate using either:

1. **Artisan Command** (Recommended) - `php artisan migrate:from-ci`
2. **PHP Script** - `php copy_data_from_ci.php`

---

## Step-by-Step Migration

### Method 1: Using Artisan Command (Recommended)

The artisan command provides interactive menu and validation features.

#### Interactive Mode (Easiest)

```bash
php artisan migrate:from-ci
```

This will:

1. Show available database configurations
2. Ask you to select which database to migrate
3. Check table/column matching
4. Ask for confirmation before migrating data

#### Command Line Mode

**Kuwait Database (Laravel Server on Port 8000)**:

```bash
php artisan migrate:from-ci \
    --source=rullart_rullart \
    --target=rullart_kuwaitbeta_laravel \
    --host=127.0.0.1 \
    --port=3306 \
    --username=root \
    --password=
```

**Qatar Database (Laravel Server on Port 8001)**:

```bash
php artisan migrate:from-ci \
    --source=rullart_rullart_qatarbeta \
    --target=rullart_qatarbeta_laravel \
    --host=127.0.0.1 \
    --port=3306 \
    --username=root \
    --password=
```

#### Check Table/Column Matching Only

To validate without migrating data:

```bash
php artisan migrate:from-ci --source=rullart_rullart --target=rullart_kuwaitbeta_laravel --check-only
```

#### Force Migration (Overwrite Existing Data)

```bash
php artisan migrate:from-ci --source=rullart_rullart --target=rullart_kuwaitbeta_laravel --force
```

### Method 2: Using PHP Script

#### Step 1: Update Script Configuration

Edit `copy_data_from_ci.php` and update database names:

```php
// CI Database Configuration
$ciConfig = [
    'database' => 'rullart_rullart',  // or 'rullart_rullart_qatarbeta'
    // ... other config
];

// Laravel Database Configuration (from .env)
$laravelConfig = [
    'database' => env('DB_DATABASE', 'rullart_kuwaitbeta_laravel'),
    // ... other config
];
```

#### Step 2: Update .env

Update `.env` with target database:

```env
DB_DATABASE=rullart_kuwaitbeta_laravel
```

#### Step 3: Run Migrations

First, create table structure:

```bash
php artisan migrate
```

#### Step 4: Copy Data

```bash
php copy_data_from_ci.php
```

---

## Multi-Database Setup

### Running Multiple Instances

To run both Kuwait and Qatar instances simultaneously:

#### Instance 1: Kuwait (Laravel Server on Port 8000)

1. **Update `.env`**:

```env
DB_DATABASE=rullart_kuwaitbeta_laravel
APP_URL=http://localhost:8000
```

2. **Run migrations**:

```bash
php artisan migrate
```

3. **Migrate data**:

```bash
php artisan migrate:from-ci --source=rullart_rullart --target=rullart_kuwaitbeta_laravel
```

4. **Start server**:

```bash
php artisan serve --port=8000
```

Access at: `http://127.0.0.1:8000`

#### Instance 2: Qatar (Laravel Server on Port 8001)

1. **Create `.env.8001` or update `.env`**:

```env
DB_DATABASE=rullart_qatarbeta_laravel
APP_URL=http://localhost:8001
```

2. **Run migrations** (with different database):

```bash
# Option 1: Update .env and run
php artisan migrate

# Option 2: Use database connection parameter
php artisan migrate --database=mysql_qatar
```

3. **Migrate data**:

```bash
php artisan migrate:from-ci --source=rullart_rullart_qatarbeta --target=rullart_qatarbeta_laravel
```

4. **Start server**:

```bash
php artisan serve --port=8001
```

Access at: `http://127.0.0.1:8001`

### Alternative: Multiple Database Connections

You can configure multiple database connections in `config/database.php`:

```php
'connections' => [
    'mysql' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'database' => env('DB_DATABASE', 'rullart_kuwaitbeta_laravel'),
        // ...
    ],
    'mysql_qatar' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'database' => env('DB_DATABASE_QATAR', 'rullart_qatarbeta_laravel'),
        // ...
    ],
],
```

Then use:

```php
DB::connection('mysql_qatar')->table('customers')->get();
```

---

## Table & Column Validation

### What Gets Validated

The migration command automatically validates:

1. **Table Existence** - Checks if tables from CI exist in Laravel
2. **Column Matching** - Compares columns between CI and Laravel tables
3. **Data Types** - Ensures compatible data types (handled during migration)

### Validation Output

When you run `php artisan migrate:from-ci --check-only`, you'll see:

```
=== Table Matching Results ===

+------------------+--------+-------------+-----------------+----------+---------+---------------+
| Table            | Exists | CI Columns  | Laravel Columns | Matching | Missing | Status        |
+------------------+--------+-------------+-----------------+----------+---------+---------------+
| customers        | ✓      | 25          | 25              | 25       | -       | PERFECT MATCH |
| ordermaster      | ✓      | 50          | 48              | 48       | 2       | MISSING COLS  |
| products         | ✓      | 30          | 30              | 30       | -       | PERFECT MATCH |
| missing_table    | ✗      | 15          | 0               | 0        | 15      | MISSING TABLE |
+------------------+--------+-------------+-----------------+----------+---------+---------------+

Summary:
  Total CI Tables: 66
  Tables in Laravel: 65
  Perfect Matches: 63
  Missing Tables: 1
```

### Handling Missing Tables

If tables are missing, you need to:

1. **Check migrations**: Ensure all migrations are created
2. **Run migrations**: `php artisan migrate`
3. **Check migration files**: Look in `database/migrations/`

### Handling Missing Columns

If columns are missing:

1. **Create migration**: `php artisan make:migration add_columns_to_table_name`
2. **Add columns** in migration file
3. **Run migration**: `php artisan migrate`
4. **Re-run validation**: `php artisan migrate:from-ci --check-only`

---

## Complete Migration Workflow

### For Each Database Instance

#### Step 1: Prepare Environment

```bash
# 1. Update .env with target database
DB_DATABASE=rullart_kuwaitbeta_laravel  # or rullart_qatarbeta_laravel

# 2. Generate app key (if not done)
php artisan key:generate

# 3. Clear cache
php artisan config:clear
php artisan cache:clear
```

#### Step 2: Create Database Structure

```bash
# Run all migrations to create tables
php artisan migrate

# Check migration status
php artisan migrate:status
```

#### Step 3: Validate Structure

```bash
# Check table/column matching
php artisan migrate:from-ci \
    --source=rullart_rullart \
    --target=rullart_kuwaitbeta_laravel \
    --check-only
```

**Fix any missing tables or columns before proceeding.**

#### Step 4: Migrate Data

```bash
# Interactive mode (recommended for first time)
php artisan migrate:from-ci

# Or direct command
php artisan migrate:from-ci \
    --source=rullart_rullart \
    --target=rullart_kuwaitbeta_laravel \
    --force
```

#### Step 5: Verify Migration

```bash
# Check data counts
php artisan tinker

# In tinker:
DB::table('customers')->count();
DB::table('ordermaster')->count();
DB::table('products')->count();
```

#### Step 6: Create Admin User

```bash
php artisan db:seed --class=AdminSeeder
```

Or manually:

```bash
php artisan tinker
```

```php
DB::table('admin')->insert([
    'id' => 1,
    'user' => 'admin',
    'pass' => md5('password'),
    'name' => 'Administrator',
    'email' => 'admin@rullart.com',
    'site' => 0,
    'user_role' => 1,
    'lock_access' => 0,
    'fkstoreid' => 1,
    'created_date' => now(),
]);
```

#### Step 7: Start Application

```bash
# For Kuwait (port 8000)
php artisan serve --port=8000

# For Qatar (port 8001) - in separate terminal
php artisan serve --port=8001
```

---

## Troubleshooting

### Error: "Table doesn't exist"

**Cause**: Migration hasn't been run or migration file is missing.

**Solution**:

```bash
# Run migrations
php artisan migrate

# Check if migration exists
ls database/migrations/ | grep table_name

# If missing, check if table should exist in CI database
```

### Error: "Invalid datetime format: 0000-00-00"

**Cause**: CI database has invalid MySQL dates.

**Solution**: The migration script automatically handles this by converting `0000-00-00` to `NULL`. If you see this error, check the migration script handles datetime properly.

### Error: "Duplicate entry"

**Cause**: Data already exists in target database.

**Solution**:

```bash
# Use --force flag to overwrite
php artisan migrate:from-ci --source=... --target=... --force

# Or truncate table first
php artisan tinker
DB::table('table_name')->truncate();
```

### Error: "Connection refused" or "Access denied"

**Cause**: Database credentials incorrect or database doesn't exist.

**Solution**:

1. Verify database exists: `SHOW DATABASES;`
2. Check credentials in `.env`
3. Test connection: `mysql -u root -p -e "USE database_name; SHOW TABLES;"`

### Error: "Column count doesn't match"

**Cause**: Table structures don't match between CI and Laravel.

**Solution**:

```bash
# Check matching
php artisan migrate:from-ci --source=... --target=... --check-only

# Compare columns manually
# CI: SHOW COLUMNS FROM table_name;
# Laravel: DB::select("SHOW COLUMNS FROM table_name");
```

### Error: "These credentials do not match" (Admin Login)

**Cause**: Admin user doesn't exist or password hash incorrect.

**Solution**:

```bash
# Check admin user
php artisan tinker
DB::table('admin')->get();

# Update password
DB::table('admin')->where('user', 'admin')->update(['pass' => md5('password')]);
```

### Migration Stuck or Very Slow

**Cause**: Large tables or missing indexes.

**Solution**:

1. Check table sizes in CI database
2. Consider migrating in batches
3. Add indexes after migration
4. Check MySQL slow query log

---

## Best Practices

### 1. Always Validate First

Before migrating data, always check table/column matching:

```bash
php artisan migrate:from-ci --source=... --target=... --check-only
```

### 2. Backup Before Migration

```bash
# Backup CI database
mysqldump -u root rullart_rullart > backup_ci_$(date +%Y%m%d).sql

# Backup Laravel database (before migration)
mysqldump -u root rullart_kuwaitbeta_laravel > backup_laravel_$(date +%Y%m%d).sql
```

### 3. Migrate in Order

Migrate dependent tables first:

1. Master data (countries, categories, etc.)
2. Customers
3. Products
4. Orders
5. Order items
6. Other dependent tables

The migration script handles this automatically by checking foreign keys.

### 4. Test After Migration

```bash
# Check record counts match
php artisan tinker

# Compare counts
$ciCount = // from CI database
$laravelCount = DB::table('table_name')->count();
echo "CI: $ciCount, Laravel: $laravelCount\n";
```

### 5. Handle Large Tables Separately

For very large tables (>100K records):

```bash
# Migrate specific table
# Edit copy_data_from_ci.php to only include that table
php copy_data_from_ci.php
```

### 6. Use Transactions

The migration script uses transactions, so if migration fails, data is rolled back.

### 7. Monitor Migration Progress

Watch for:

-   Error messages
-   Record counts
-   Processing time
-   Memory usage

---

## Command Reference

### Artisan Migration Command

```bash
# Interactive mode
php artisan migrate:from-ci

# With options
php artisan migrate:from-ci [options]

Options:
  --source=SOURCE         CI Source database name
  --target=TARGET         Laravel Target database name
  --host=HOST             Database host (default: 127.0.0.1)
  --port=PORT             Database port (default: 3306)
  --username=USERNAME     Database username (default: root)
  --password=PASSWORD     Database password
  --force                 Force migration even if target has data
  --check-only            Only check table/column matching
  -h, --help              Display help message
```

### Examples

```bash
# Check Kuwait database matching
php artisan migrate:from-ci \
    --source=rullart_rullart \
    --target=rullart_kuwaitbeta_laravel \
    --check-only

# Migrate Qatar database with force
php artisan migrate:from-ci \
    --source=rullart_rullart_qatarbeta \
    --target=rullart_qatarbeta_laravel \
    --force

# Migrate with custom credentials
php artisan migrate:from-ci \
    --source=rullart_rullart \
    --target=rullart_kuwaitbeta_laravel \
    --username=myuser \
    --password=mypassword
```

### Standard Laravel Commands

```bash
# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Check migration status
php artisan migrate:status

# Seed database
php artisan db:seed

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Tinker (database shell)
php artisan tinker
```

---

## Project Structure

```
rullart/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── MigrateFromCI.php          # Migration command
│   ├── Models/                            # Eloquent models (based on CI structure)
│   └── Http/Controllers/Admin/            # Admin controllers
├── database/
│   ├── migrations/                        # All table migrations (70+ files)
│   └── seeders/
│       └── AdminSeeder.php                # Admin user seeder
├── copy_data_from_ci.php                  # Legacy migration script
├── MIGRATION_GUIDE.md                     # This file
└── .env                                   # Environment configuration
```

---

## Quick Reference

### Kuwait Database Migration

```bash
# 1. Setup
export DB_DATABASE=rullart_kuwaitbeta_laravel
php artisan migrate

# 2. Migrate
php artisan migrate:from-ci \
    --source=rullart_rullart \
    --target=rullart_kuwaitbeta_laravel

# 3. Run
php artisan serve --port=8000
```

### Qatar Database Migration

```bash
# 1. Setup
export DB_DATABASE=rullart_qatarbeta_laravel
php artisan migrate

# 2. Migrate
php artisan migrate:from-ci \
    --source=rullart_rullart_qatarbeta \
    --target=rullart_qatarbeta_laravel

# 3. Run
php artisan serve --port=8001
```

---

## Support & Additional Resources

### Key Files

-   `app/Console/Commands/MigrateFromCI.php` - Migration command
-   `copy_data_from_ci.php` - Legacy migration script
-   `database/migrations/` - All table migrations
-   `config/database.php` - Database configuration
-   `.env` - Environment variables

### Getting Help

1. Check this guide first
2. Run `--check-only` to validate structure
3. Check Laravel logs: `storage/logs/laravel.log`
4. Verify database connections
5. Compare CI and Laravel table structures

---

**Last Updated**: December 2025  
**Version**: 2.0  
**Laravel Version**: 12.x
