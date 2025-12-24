# Setup Laravel with CI Databases Directly

## Overview

This setup allows Laravel to use existing CI databases directly without running migrations. Only Laravel system tables are created.

## Database Configuration

### Port 8000 (Kuwait)
- **Database**: `rullart_rullart_kuwaitbeta`
- **URL**: `http://127.0.0.1:8000`

### Port 8001 (Qatar)
- **Database**: `rullart_rullart_qatarbeta`
- **URL**: `http://127.0.0.1:8001`

## Setup Steps

### Step 1: Update .env File

For **Port 8000** (Kuwait):
```env
DB_DATABASE=rullart_rullart_kuwaitbeta
APP_URL=http://127.0.0.1:8000
```

For **Port 8001** (Qatar):
```env
DB_DATABASE=rullart_rullart_qatarbeta
APP_URL=http://127.0.0.1:8001
```

### Step 2: Create Laravel System Tables

Run the script to create only Laravel system tables:

```bash
php create_laravel_system_tables.php
```

This will create:
- `migrations` - Tracks migration status
- `cache` - Cache storage
- `cache_locks` - Cache locks
- `sessions` - Session storage
- `jobs` - Queue jobs
- `job_batches` - Job batches
- `failed_jobs` - Failed jobs

### Step 3: Fix Admin Password

After database setup, fix admin credentials:

```bash
php fix_admin_username.php
```

### Step 4: Start Laravel Server

**For Kuwait (Port 8000):**
```bash
php artisan serve --port=8000
```

**For Qatar (Port 8001):**
```bash
php artisan serve --port=8001
```

## Important Notes

1. **No Migrations**: Do NOT run `php artisan migrate` - it will try to create all tables
2. **CI Tables**: All CI tables already exist in the database
3. **System Tables Only**: Only Laravel system tables are created
4. **Database Views**: If you need views like `productpriceview`, create them manually in the database

## Troubleshooting

### Error: Table doesn't exist

If you get errors about missing tables:
1. Check if the table exists in CI database
2. If it's a Laravel system table, run `php create_laravel_system_tables.php`
3. If it's a CI table, it should already exist

### Error: Database connection failed

1. Verify database name in `.env` matches CI database name
2. Check MySQL credentials
3. Ensure database exists: `SHOW DATABASES;`

### Missing Views

If you need database views (like `productpriceview`), create them manually:

```sql
CREATE VIEW `productpriceview` AS 
SELECT 
    `products`.`productid` AS `fkproductid`,
    `products`.`discount` AS `discount`,
    `products`.`sellingprice` AS `sellingprice` 
FROM `products`;
```

## Quick Setup Commands

```bash
# 1. Update .env with correct database
# DB_DATABASE=rullart_rullart_kuwaitbeta (for port 8000)
# DB_DATABASE=rullart_rullart_qatarbeta (for port 8001)

# 2. Create Laravel system tables
php create_laravel_system_tables.php

# 3. Fix admin password
php fix_admin_username.php

# 4. Start server
php artisan serve --port=8000  # or 8001
```

