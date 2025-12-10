<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AdminLoginController;
use App\Http\Controllers\RoutingController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application.
|
*/

// Admin Login Routes (Guest only)
Route::middleware('guest')->prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminLoginController::class, 'create'])->name('login');
    Route::post('login', [AdminLoginController::class, 'store'])->name('login.store');
});

// Admin Protected Routes
Route::middleware(['auth:admin', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('logout', [AdminLoginController::class, 'destroy'])->name('logout');

    // Dashboard
    Route::get('', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index']);

    // Manage Orders
    Route::get('customers', [\App\Http\Controllers\Admin\CustomerController::class, 'index'])->name('customers');
    Route::get('orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders');
    Route::get('ordersnotprocess', function () {
        return view('admin.orders-not-process');
    })->name('orders-not-process');
    Route::get('wishlist', function () {
        return view('admin.wishlist');
    })->name('wishlist');
    Route::get('productrate', function () {
        return view('admin.product-rate');
    })->name('product-rate');
    Route::get('mobiledevice', function () {
        return view('admin.mobile-device');
    })->name('mobile-device');
    Route::get('returnrequest', function () {
        return view('admin.return-request');
    })->name('return-request');

    // Manage Products
    Route::get('category', function () {
        return view('admin.category');
    })->name('category');
    Route::get('occassion', function () {
        return view('admin.occassion');
    })->name('occassion');
    Route::get('products', function () {
        return view('admin.products');
    })->name('products');
    Route::get('giftproduct2', function () {
        return view('admin.gift-product-4');
    })->name('gift-product-4');
    Route::get('giftproducts', function () {
        return view('admin.gift-products');
    })->name('gift-products');

    // Reports
    Route::get('sales-report-date', function () {
        return view('admin.reports.sales-report-date');
    })->name('sales-report-date');
    Route::get('sales-report-month', function () {
        return view('admin.reports.sales-report-month');
    })->name('sales-report-month');
    Route::get('sales-report-year', function () {
        return view('admin.reports.sales-report-year');
    })->name('sales-report-year');
    Route::get('sales-report-customer', function () {
        return view('admin.reports.sales-report-customer');
    })->name('sales-report-customer');
    Route::get('top-product-month', function () {
        return view('admin.reports.top-product-month');
    })->name('top-product-month');
    Route::get('top-product-rate', function () {
        return view('admin.reports.top-product-rate');
    })->name('top-product-rate');

    // Masters
    Route::get('colors', function () {
        return view('admin.masters.colors');
    })->name('colors');
    Route::get('areas', function () {
        return view('admin.masters.areas');
    })->name('areas');
    Route::get('countries', function () {
        return view('admin.masters.countries');
    })->name('countries');
    Route::get('sizes', function () {
        return view('admin.masters.sizes');
    })->name('sizes');
    Route::get('couponcode', function () {
        return view('admin.masters.coupon-code');
    })->name('coupon-code');
    Route::get('discounts', function () {
        return view('admin.masters.discounts');
    })->name('discounts');
    Route::get('couriercompany', function () {
        return view('admin.masters.courier-company');
    })->name('courier-company');
    Route::get('messages', function () {
        return view('admin.masters.messages');
    })->name('messages');

    // Manage Pages
    Route::get('homegallery', function () {
        return view('admin.pages.home-gallery');
    })->name('home-gallery');
    Route::get('pages/home', function () {
        return view('admin.pages.home');
    })->name('pages.home');
    Route::get('pages/aboutus', function () {
        return view('admin.pages.aboutus');
    })->name('pages.aboutus');
    Route::get('pages/corporate-gift', function () {
        return view('admin.pages.corporate-gift');
    })->name('pages.corporate-gift');
    Route::get('pages/franchises', function () {
        return view('admin.pages.franchises');
    })->name('pages.franchises');
    Route::get('pages/contactus', function () {
        return view('admin.pages.contactus');
    })->name('pages.contactus');
    Route::get('pages/shipping', function () {
        return view('admin.pages.shipping');
    })->name('pages.shipping');
    Route::get('pages/terms', function () {
        return view('admin.pages.terms');
    })->name('pages.terms');
    Route::get('pages/newsletter', function () {
        return view('admin.pages.newsletter');
    })->name('pages.newsletter');

    // Settings
    Route::get('settings', function () {
        return view('admin.settings');
    })->name('settings');

    // Analytics
    Route::get('analytics-dashboard', function () {
        return view('admin.analytics.dashboard');
    })->name('analytics-dashboard');
});
