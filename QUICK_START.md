# Quick Start Guide

## 🚀 Getting Started in 5 Minutes

### Step 1: Install Dependencies
```bash
cd design-rullart
composer install
```

### Step 2: Configure Database
Edit `.env` file:
```env
DB_DATABASE=laravel_123
DB_USERNAME=root
DB_PASSWORD=
```

### Step 3: Run Migrations
```bash
php artisan migrate
```

### Step 4: Fix Missing Tables (if any)
```bash
php fix_all_missing_tables.php
```

### Step 5: Create Admin User
```bash
php fix_admin_password.php
```

### Step 6: Start Server
```bash
php artisan serve
```

### Step 7: Login
- URL: `http://127.0.0.1:8000/admin/login`
- Username: `admin` (or `info@rullart.com`)
- Password: `password` (or `rullart@2025`)

## 🔧 Common Commands

### Fix Issues
```bash
# Fix all missing tables
php fix_all_missing_tables.php

# Update admin password
php fix_admin_password.php

# Clear all caches
php artisan config:clear
php artisan cache:clear
```

### Copy Data from CI Database
```bash
php copy_data_from_ci.php
```

## 📚 Full Documentation
See `MIGRATION_GUIDE.md` for complete documentation.

## ⚠️ Troubleshooting

**Table doesn't exist?**
→ Run `php fix_all_missing_tables.php`

**Can't login?**
→ Run `php fix_admin_password.php`

**Session errors?**
→ Clear cache: `php artisan config:clear`

---

**Need Help?** Check `MIGRATION_GUIDE.md` for detailed information.

