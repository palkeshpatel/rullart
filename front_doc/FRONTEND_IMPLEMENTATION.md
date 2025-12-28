# Frontend Implementation Guide - Rullart E-commerce

## Overview

This document describes the frontend implementation for the Rullart e-commerce website, migrated from CodeIgniter to Laravel. The frontend maintains the same design and functionality as the original CI implementation while leveraging Laravel's modern framework capabilities.

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Directory Structure](#directory-structure)
3. [Key Components](#key-components)
4. [Routes Structure](#routes-structure)
5. [Controllers](#controllers)
6. [Views & Layouts](#views--layouts)
7. [Middleware](#middleware)
8. [Configuration](#configuration)
9. [Features Implemented](#features-implemented)
10. [Features To Be Implemented](#features-to-be-implemented)
11. [Assets Migration](#assets-migration)
12. [Setup Instructions](#setup-instructions)
13. [Development Guidelines](#development-guidelines)

---

## Architecture Overview

The frontend follows Laravel's MVC pattern with the following key components:

-   **FrontendController**: Base controller for all frontend controllers, handles common functionality
-   **Language Support**: Multi-language (English/Arabic) with URL prefixes (`/en/`, `/ar/`)
-   **Currency Management**: Dynamic currency switching based on country/IP
-   **Session Management**: Shopping cart and user session handling
-   **Blade Templates**: Modern template engine with reusable components

---

## Directory Structure

```
rullart/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Frontend/
│   │   │       ├── FrontendController.php      # Base controller
│   │   │       ├── HomeController.php          # Homepage
│   │   │       ├── ProductController.php       # Product pages
│   │   │       ├── CategoryController.php      # Category/Listing pages
│   │   │       ├── ShoppingCartController.php  # Shopping cart
│   │   │       ├── CheckoutController.php      # Checkout process
│   │   │       ├── AuthController.php          # Authentication
│   │   │       ├── ProfileController.php       # User profile
│   │   │       ├── OrderController.php         # Order history
│   │   │       ├── WishlistController.php      # Wishlist
│   │   │       ├── AddressController.php       # Address management
│   │   │       ├── SearchController.php        # Search functionality
│   │   │       └── PageController.php          # Static pages
│   │   └── Middleware/
│   │       ├── SetLocale.php                   # Language middleware
│   │       └── SetCurrency.php                 # Currency middleware
├── resources/
│   └── views/
│       └── frontend/
│           ├── layouts/
│           │   ├── app.blade.php               # Main layout
│           │   └── partials/
│           │       ├── header.blade.php        # Header partial
│           │       └── footer.blade.php        # Footer partial
│           ├── home/
│           │   └── index.blade.php             # Homepage
│           ├── product/
│           │   └── show.blade.php              # Product detail
│           ├── category/
│           │   └── index.blade.php             # Category listing
│           ├── cart/
│           ├── checkout/
│           ├── auth/
│           ├── account/
│           └── page/
├── routes/
│   └── frontend.php                            # Frontend routes
└── config/
    └── app.php                                 # App configuration
```

---

## Key Components

### 1. FrontendController (Base Controller)

Located at: `app/Http/Controllers/Frontend/FrontendController.php`

**Responsibilities:**

-   Initialize settings from database
-   Set locale (language)
-   Initialize currency based on IP/session
-   Load category and occasion menus
-   Share common data with all views
-   Provide cart and wishlist count methods

**Key Methods:**

-   `initializeSettings()` - Load settings from database
-   `initializeLocale()` - Set application locale
-   `initializeCurrency()` - Set currency based on country/IP
-   `initializeMenus()` - Load category and occasion menus
-   `getCartCount()` - Get shopping cart item count
-   `getWishlistCount()` - Get wishlist item count

### 2. Middleware

#### SetLocale Middleware

-   Sets application locale from URL parameter or session
-   Validates locale (only 'en' or 'ar' allowed)
-   Stores locale in session

#### SetCurrency Middleware

-   Detects country from IP (simplified implementation)
-   Sets currency code and rate in session
-   Refreshes currency rate every 10 minutes
-   Falls back to default currency if country not found

---

## Routes Structure

### Route File

Location: `routes/frontend.php`

### Route Groups

1. **Language Routes** (`/{locale}/`)

    - Supports both `/` (default) and `/{locale}/` (en/ar)
    - All routes are wrapped in `locale` and `currency` middleware

2. **Main Routes**

    - `/` or `/{locale}/` - Homepage
    - `/product/{category}/{product}` - Product detail
    - `/category/{category}` - Category listing
    - `/shoppingcart` - Shopping cart
    - `/checkout` - Checkout
    - `/login`, `/register` - Authentication
    - `/myprofile`, `/myorders`, etc. - User account (requires auth)

3. **Utility Routes**
    - `/language/{locale}` - Switch language
    - `/currency/{code}` - Switch currency

### Route Naming Convention

All routes use named routes for easy URL generation:

-   `home` - Homepage
-   `product.show` - Product detail
-   `category.index` - Category listing
-   `cart.index` - Shopping cart
-   etc.

---

## Controllers

### HomeController

-   Displays homepage with hero slider (home gallery)
-   Shows popular products
-   Loads page data for SEO

### ProductController

-   Product detail page
-   Handles product code redirects
-   Validates category/product matching

### CategoryController

-   Category product listing
-   "All products" page
-   Products by occasion
-   Sale page
-   What's New page

### PageController

-   Static pages (About, Contact, Terms, etc.)
-   Uses Page model for content management

### AuthController

-   Login/Register
-   Password reset
-   Logout
-   Session management (mimics CI behavior)

### Other Controllers (To Be Implemented)

-   ShoppingCartController
-   CheckoutController
-   ProfileController
-   OrderController
-   WishlistController
-   AddressController
-   SearchController

---

## Views & Layouts

### Main Layout

Location: `resources/views/frontend/layouts/app.blade.php`

**Features:**

-   Multi-language HTML attributes
-   RTL support for Arabic
-   SEO meta tags
-   Open Graph tags
-   Font loading (Arabic vs English)
-   CSS/JS asset loading
-   Analytics integration

### Header Partial

Location: `resources/views/frontend/layouts/partials/header.blade.php`

**Components:**

-   Logo
-   Main navigation (Category, Occasion, etc.)
-   Language switcher
-   User menu (Login/Account)
-   Shopping cart icon with count
-   Wishlist icon with count
-   Mobile menu toggle

### Footer Partial

Location: `resources/views/frontend/layouts/partials/footer.blade.php`

**Components:**

-   Footer logo
-   App store links
-   Social media links
-   Footer navigation
-   Copyright information

### Homepage View

Location: `resources/views/frontend/home/index.blade.php`

**Features:**

-   Hero slider (supports video)
-   Welcome content section
-   Popular products grid

---

## Middleware

### Registration

Location: `bootstrap/app.php`

```php
$middleware->alias([
    'admin' => \App\Http\Middleware\Admin::class,
    'locale' => \App\Http\Middleware\SetLocale::class,
    'currency' => \App\Http\Middleware\SetCurrency::class,
]);
```

### Usage

Applied to all frontend routes via route group:

```php
Route::middleware(['web', 'locale', 'currency'])->group(function () {
    // Frontend routes
});
```

---

## Configuration

### App Configuration

Location: `config/app.php`

Added configuration values:

```php
'resource_url' => env('RESOURCE_URL', url('/resources/')),
'image_url' => env('IMAGE_URL', url('/resources/')),
'default_country' => env('DEFAULT_COUNTRY', 'Kuwait'),
'default_currencycode' => env('DEFAULT_CURRENCYCODE', 'KWD'),
```

### Environment Variables

Add to `.env`:

```env
RESOURCE_URL=https://yourdomain.com/resources/
IMAGE_URL=https://yourdomain.com/resources/
DEFAULT_COUNTRY=Kuwait
DEFAULT_CURRENCYCODE=KWD
```

---

## Features Implemented

✅ **Completed:**

1. Route structure with language support
2. Base FrontendController with common functionality
3. Middleware for locale and currency
4. Main layout with header and footer
5. Homepage with gallery slider and popular products
6. Product detail page structure
7. Category listing page structure
8. Authentication (login/register/logout)
9. Static pages structure
10. Language switching
11. Currency switching

⏳ **In Progress / To Be Implemented:**

1. Shopping cart functionality
2. Checkout process
3. Payment gateway integration
4. User profile management
5. Order history
6. Address management
7. Wishlist functionality
8. Search functionality
9. Product filtering (color, size, price)
10. Product sorting
11. Pagination
12. Email notifications
13. Product reviews/ratings
14. Gift wrapping options
15. Coupon code application

---

## Assets Migration

### Current Status

The frontend layout expects assets at `/resources/` path.

### Required Assets (from CI project)

Copy from `ruralt-ci/resources/` to Laravel `public/resources/`:

1. **Styles:**

    - `styles/main.css`
    - `styles/main-ar.css`
    - `styles/custom.css`
    - `styles/custom-ar.css`

2. **Scripts:**

    - `scripts/scripts.js`
    - `scripts/plugins.min.js`
    - `scripts/main.js`
    - `scripts/common.js`
    - `scripts/custom.js`
    - Other page-specific scripts

3. **Images:**

    - `images/` directory
    - Logo files
    - App store badges
    - SVG icons

4. **Fonts:**
    - Font files if any

### Asset Structure

```
public/
└── resources/
    ├── styles/
    ├── scripts/
    ├── images/
    └── storage/ (product images)
```

---

## Setup Instructions

### 1. Install Dependencies

```bash
cd rullart
composer install
npm install
```

### 2. Configure Environment

Copy `.env.example` to `.env` and configure:

```env
APP_URL=http://localhost:8000
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

RESOURCE_URL=http://localhost:8000/resources/
IMAGE_URL=http://localhost:8000/resources/
DEFAULT_COUNTRY=Kuwait
DEFAULT_CURRENCYCODE=KWD
```

### 3. Generate Application Key

```bash
php artisan key:generate
```

### 4. Copy Assets

Copy frontend assets from CI project:

```bash
# Copy CSS/JS/Images from ruralt-ci/resources/ to public/resources/
```

### 5. Create Storage Link

```bash
php artisan storage:link
```

### 6. Run Migrations (if needed)

```bash
php artisan migrate
```

### 7. Start Development Server

```bash
php artisan serve
```

### 8. Access Frontend

-   Homepage: http://localhost:8000/ or http://localhost:8000/en/
-   Arabic: http://localhost:8000/ar/

---

## Development Guidelines

### 1. Controller Guidelines

-   All frontend controllers should extend `FrontendController`
-   Use named routes instead of hardcoded URLs
-   Pass data to views using descriptive variable names
-   Handle 404 cases properly using `abort(404)`

### 2. View Guidelines

-   Use Blade syntax (`{{ }}`, `@if`, `@foreach`, etc.)
-   Keep views clean and focused on presentation
-   Move complex logic to controllers or helpers
-   Use `@extends` for layout inheritance
-   Use `@include` for reusable partials
-   Use `@stack` for page-specific scripts/styles

### 3. Route Guidelines

-   Use named routes for all routes
-   Group related routes logically
-   Use route parameters for dynamic content
-   Apply appropriate middleware

### 4. Language/Locale

-   Use Laravel's translation system: `__('key')` or `@lang('key')`
-   Store translations in `resources/lang/{locale}/`
-   Use locale-specific content from database when available
-   Always provide both English and Arabic content

### 5. Currency

-   Always multiply prices by `$currencyRate` before display
-   Store prices in base currency in database
-   Display currency code alongside price
-   Format prices appropriately (3 decimal places for KWD)

### 6. Session Management

-   Use Laravel's Session facade
-   Store user data: `customerid`, `firstname`, `email`, `logged_in`
-   Store cart data: `shoppingcartid`
-   Clear session on logout

---

## Next Steps

### Priority 1 (Critical)

1. Complete ShoppingCartController
2. Complete CheckoutController
3. Implement payment gateway integration
4. Test all existing functionality

### Priority 2 (Important)

5. Complete user account pages (Profile, Orders, Addresses)
6. Implement wishlist functionality
7. Implement search functionality
8. Add product filtering and sorting

### Priority 3 (Enhancements)

9. Product reviews and ratings
10. Email notifications
11. Gift wrapping options
12. Advanced search
13. Product recommendations
14. Recently viewed products

---

## Troubleshooting

### Routes Not Working

-   Check `routes/frontend.php` is included in `routes/web.php`
-   Verify middleware is registered in `bootstrap/app.php`
-   Clear route cache: `php artisan route:clear`

### Assets Not Loading

-   Verify `RESOURCE_URL` in `.env` and `config/app.php`
-   Check assets exist in `public/resources/`
-   Clear config cache: `php artisan config:clear`

### Language Not Switching

-   Check `SetLocale` middleware is applied
-   Verify locale in session
-   Check routes have `{locale?}` parameter

### Currency Issues

-   Verify `SetCurrency` middleware is applied
-   Check country detection logic
-   Verify currency data in database

---

## Support & Documentation

-   Laravel Documentation: https://laravel.com/docs
-   Blade Templates: https://laravel.com/docs/blade
-   Original CI Project: `ruralt-ci/`
-   Admin Panel: Already migrated (separate documentation)

---

## Notes

-   This implementation maintains compatibility with the existing database structure
-   Session management mimics CI behavior for smooth transition
-   All URLs should match CI structure for SEO purposes
-   Multi-language support is built-in but translations need to be added
-   Currency conversion is simplified and may need enhancement based on requirements

---

**Last Updated:** {{ date('Y-m-d') }}
**Version:** 1.0
**Status:** Initial Implementation
