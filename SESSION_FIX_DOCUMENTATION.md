# Session Fix Documentation - Multi-Tenant Database Setup

## 🔴 Problem

Session IDs were changing on every page load, causing cart count and other session data to be lost. This occurred because:

1. **Multi-tenant database switching**: The application switches databases dynamically based on domain/port
2. **Session connection mismatch**: Sessions were using the same `mysql` connection that gets purged/reconnected during database switching
3. **Lost session data**: When the database connection was switched, Laravel couldn't find existing sessions in the new database context

## ✅ Solution

Created a dedicated session database connection that is synchronized with the tenant database but managed independently, preventing session loss during database switching.

---

## 📋 Changes Made

### 1. Created Dedicated Session Connection (`config/database.php`)

Added a new `session` connection that uses the same credentials as the main `mysql` connection but can be managed independently:

```php
'session' => [
    'driver' => 'mysql',
    'url' => env('DB_URL'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => env('DB_DATABASE', 'laravel'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    'unix_socket' => env('DB_SOCKET', ''),
    'charset' => env('DB_CHARSET', 'utf8mb4'),
    'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
    'prefix' => '',
    'prefix_indexes' => true,
    'strict' => true,
    'engine' => null,
    'options' => extension_loaded('pdo_mysql') ? array_filter([
        PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
    ]) : [],
],
```

### 2. Updated Session Configuration (`config/session.php`)

Changed the session connection to use the dedicated `session` connection:

```php
'connection' => env('SESSION_CONNECTION', 'session'),
```

### 3. Enhanced Database Switching Logic (`app/Providers/AppServiceProvider.php`)

Updated `applyDatabaseSwitch()` method to:
- Synchronize both `mysql` and `session` connections to the same tenant database
- Handle cases where session connection is initialized before/after database switching
- Prevent session connection loss during database switches

Added `syncSessionConnection()` method as a fallback to ensure session connection is always in sync.

---

## 🗄️ SQL Queries Required

### IMPORTANT: Sessions Table Must Exist in ALL Tenant Databases

The `sessions` table must exist in **every tenant database** for sessions to work properly. Run the following SQL in each tenant database.

### SQL Query to Create Sessions Table

```sql
CREATE TABLE IF NOT EXISTS `sessions` (
    `id` VARCHAR(255) NOT NULL,
    `user_id` BIGINT UNSIGNED DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` TEXT,
    `payload` LONGTEXT NOT NULL,
    `last_activity` INT NOT NULL,
    PRIMARY KEY (`id`),
    KEY `sessions_user_id_index` (`user_id`),
    KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

---

## 📝 Step-by-Step Setup Instructions

### Step 1: Create Sessions Table in All Tenant Databases

For each tenant database, run the SQL query above. 

#### Using MySQL Command Line:

```bash
# Connect to MySQL
mysql -u root -p

# Select tenant database 1 (e.g., Kuwait)
USE rullart_kuwaitalpha;
# Run the CREATE TABLE query above

# Select tenant database 2 (e.g., Qatar)
USE rullart_qataralpha;
# Run the CREATE TABLE query above
```

#### Using phpMyAdmin or Database Tool:

1. Select the tenant database
2. Go to SQL tab
3. Paste the SQL query
4. Execute
5. Repeat for all tenant databases

#### Using Laravel Artisan (if migrations are set up):

```bash
# Switch to tenant 1 database (update .env DB_DATABASE temporarily)
php artisan migrate

# Switch to tenant 2 database (update .env DB_DATABASE temporarily)
php artisan migrate
```

---

### Step 2: Clear Configuration Cache

After making configuration changes, clear the cache:

```bash
php artisan config:clear
php artisan cache:clear
```

---

### Step 3: Verify Sessions Table Exists

Check that the `sessions` table exists in all tenant databases:

```sql
-- Run this in each tenant database
SHOW TABLES LIKE 'sessions';
DESCRIBE sessions;
```

---

### Step 4: Test the Fix

1. **Clear browser cookies** for your localhost domain
2. **Visit the application** (e.g., `http://localhost:8000/en`)
3. **Add an item to cart**
4. **Reload the page multiple times**
5. **Check that**:
   - Session ID remains the same (check browser DevTools > Application > Cookies)
   - Cart count persists after page reload
   - No new session is created on each page load

---

## 🔍 Verification Checklist

- [ ] Sessions table exists in **all tenant databases**
- [ ] Configuration cache has been cleared (`php artisan config:clear`)
- [ ] Session ID remains consistent across page loads
- [ ] Cart count persists after page reload
- [ ] Session data (like locale, currency) persists correctly

---

## 🐛 Troubleshooting

### Issue: Sessions still not working

**Check 1**: Verify sessions table exists in the tenant database you're using
```sql
-- Check which database you're using (from domain_db.php mapping)
-- Then check if sessions table exists
USE your_tenant_database;
SHOW TABLES LIKE 'sessions';
```

**Check 2**: Verify session configuration
```bash
php artisan tinker
>>> config('session.connection')  // Should return 'session'
>>> config('database.connections.session.database')  // Should show current tenant DB
```

**Check 3**: Check Laravel logs for session errors
```bash
tail -f storage/logs/laravel.log
```

### Issue: "Table 'xxx.sessions' doesn't exist" error

**Solution**: Run the SQL query above to create the sessions table in the specific database mentioned in the error.

### Issue: Session ID still changing

**Check**: Ensure `.env` doesn't have `SESSION_CONNECTION` set to something other than `session`, or remove it entirely to use the default.

---

## 📚 Related Files

- `config/database.php` - Database connections configuration
- `config/session.php` - Session configuration
- `app/Providers/AppServiceProvider.php` - Database switching logic
- `missing_laravel_table/missing_laravel_tables.sql` - SQL for creating Laravel system tables
- `create_laravel_system_tables.php` - PHP script to create system tables

---

## 🔄 Database List for Multi-Tenant Setup

Based on `config/domain_db.php`, ensure sessions table exists in:

### Local Development:
- `rullart_kuwaitalpha` (port 8000)
- `rullart_qataralpha` (port 9000)

### Production:
- `techiebrothers_betakuwait` (betakuwait.techiebrothers.in)
- `techiebrothers_betaqatar` (betaqatar.techiebrothers.in)

---

## 📌 Important Notes

1. **Sessions are tenant-specific**: Each tenant database has its own sessions table. Sessions from one tenant are not accessible from another tenant.

2. **Session connection synchronization**: The session connection automatically synchronizes with the tenant database when it switches, so you don't need to manually manage this.

3. **Cookie domain**: For local development with different ports, leave `SESSION_DOMAIN` unset in `.env` so cookies work properly on localhost.

4. **Migration compatibility**: If you run Laravel migrations, the sessions table will be created automatically via the migration in `database/migrations/0001_01_01_000000_create_users_table.php`.

---

## ✅ After Fix Completion

Once the fix is complete and verified:

1. Session IDs will remain consistent across page loads
2. Cart count will persist correctly
3. User preferences (locale, currency) will be maintained
4. Multi-tenant database switching will not affect sessions

---

**Last Updated**: After session fix implementation
**Status**: ✅ Ready for testing
