# Fix Session Driver Issue

## Problem Found

The session driver is set to `file` instead of `database`, which is why sessions are not persisting properly with multi-tenant database switching.

**Check Result:**

-   Session Driver: `file` ❌ (should be `database`)
-   Session Connection: `session` ✓ (correct)
-   Sessions table exists: YES ✓

## Solution

You need to update your `.env` file to set the session driver to `database`.

### Step 1: Update `.env` File

Open your `.env` file in the root directory and find this line:

```env
SESSION_DRIVER=file
```

Change it to:

```env
SESSION_DRIVER=database
```

Or if the line doesn't exist, add it:

```env
SESSION_DRIVER=database
SESSION_CONNECTION=session
```

### Step 2: Clear Configuration Cache

After updating `.env`, clear the configuration cache:

```bash
php artisan config:clear
```

### Step 3: Verify the Fix

Run the check script again to verify:

```bash
php check_session_table.php
```

You should see:

-   Session Driver: `database` ✓

### Step 4: Test the Application

1. Clear your browser cookies for localhost
2. Visit your application
3. Add an item to cart
4. Refresh the page
5. Verify that cart count persists

---

## Why This Matters

When using `file` driver:

-   Sessions are stored in `storage/framework/sessions/` directory
-   Session data is not synchronized with database switching
-   Session data can be lost or inconsistent across requests

When using `database` driver:

-   Sessions are stored in the `sessions` table in the database
-   Session connection is synchronized with tenant database switching
-   Session data persists correctly across page loads

---

**Status:** Fix ready - update `.env` file and clear cache.
