# Rullart - Laravel E-commerce Application

## Quick Setup Guide

### Step 1: Add Laravel System Tables to Database

Run the script to create essential Laravel system tables in your database:

```bash
php create_laravel_system_tables.php
```

This will create the following tables:
- `migrations`
- `cache`
- `cache_locks`
- `sessions`
- `jobs`
- `job_batches`
- `failed_jobs`

**Note:** Make sure your `.env` file is configured with the correct database credentials before running this script.

### Step 2: Setup Admin Login

#### Option A: Fix Admin Username and Password
```bash
php fix_admin_username.php
```
This will set the admin username to `info@rullart.com` with password `rullart@2025`.

#### Option B: Fix Admin Password Only
```bash
php fix_admin_password.php [username] [password]
```

**Examples:**
```bash
# Use default username (info@rullart.com) and default password (password)
php fix_admin_password.php

# Set custom username and password
php fix_admin_password.php admin@example.com mypassword123
```

### Step 3: Download Missing Images

Download all missing images from the live site:

```bash
php artisan images:download --type=all
```

**Available options:**
- `--type=all` - Download all images (products, homegallery, category)
- `--type=products` - Download only product images
- `--type=homegallery` - Download only home gallery images
- `--type=category` - Download only category images
- `--check-only` - Only check which images are missing, don't download
- `--source=https://www.rullart.com` - Custom source URL
- `--chunk-size=10` - Number of images to download concurrently

**Examples:**
```bash
# Download all images
php artisan images:download --type=all

# Check which images are missing (without downloading)
php artisan images:download --type=all --check-only

# Download only product images
php artisan images:download --type=products

# Download with custom source URL
php artisan images:download --type=all --source=https://www.rullart.com
```

---

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
