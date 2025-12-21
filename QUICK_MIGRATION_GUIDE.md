# Quick Migration Guide

## Quick Start

### For Kuwait Database (Laravel Server on Port 8000)

```bash
# 1. Update .env
DB_DATABASE=rullart_kuwaitbeta_laravel

# 2. Run migrations
php artisan migrate

# 3. Migrate data (interactive)
php artisan migrate:from-ci
# Select option 1 when prompted

# 4. Start server
php artisan serve --port=8000
```

Access: `http://127.0.0.1:8000`

### For Qatar Database (Laravel Server on Port 8001)

```bash
# 1. Update .env
DB_DATABASE=rullart_qatarbeta_laravel

# 2. Run migrations
php artisan migrate

# 3. Migrate data (interactive)
php artisan migrate:from-ci
# Select option 2 when prompted

# 4. Start server
php artisan serve --port=8001
```

Access: `http://127.0.0.1:8001`

## Available Commands

### Interactive Mode (Recommended)

```bash
php artisan migrate:from-ci
```

### Direct Command

```bash
# Kuwait
php artisan migrate:from-ci --source=rullart_rullart --target=rullart_kuwaitbeta_laravel

# Qatar
php artisan migrate:from-ci --source=rullart_rullart_qatarbeta --target=rullart_qatarbeta_laravel
```

### Check Only (No Data Migration)

```bash
php artisan migrate:from-ci --source=rullart_rullart --target=rullart_kuwaitbeta_laravel --check-only
```

### Force Migration (Overwrite Existing Data)

```bash
php artisan migrate:from-ci --source=rullart_rullart --target=rullart_kuwaitbeta_laravel --force
```

## Database Mapping

| Instance | CI Source                   | Laravel Target               | Laravel Server Port | MySQL Port |
| -------- | --------------------------- | ---------------------------- | ------------------- | ---------- |
| 1        | `rullart_rullart`           | `rullart_kuwaitbeta_laravel` | 8000                | 3306       |
| 2        | `rullart_rullart_qatarbeta` | `rullart_qatarbeta_laravel`  | 8001                | 3306       |

**Note**: Ports 8000/8001 are for Laravel development server (`php artisan serve`). MySQL uses port 3306 for all database connections.

## What the Command Does

1. ✅ Connects to CI source database
2. ✅ Lists all tables in CI database
3. ✅ Checks table existence in Laravel
4. ✅ Validates column matching
5. ✅ Shows matching results table
6. ✅ Migrates data (if confirmed)
7. ✅ Handles invalid dates (0000-00-00 → NULL)
8. ✅ Processes in batches (100 records)
9. ✅ Skips duplicates
10. ✅ Shows progress and summary

## For Complete Documentation

See `MIGRATION_GUIDE.md` for detailed instructions, troubleshooting, and best practices.
