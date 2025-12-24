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

// Admin Login Routes (Public access)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminLoginController::class, 'create'])->name('login');
    Route::post('login', [AdminLoginController::class, 'store'])->name('login.store');
});

// Admin Protected Routes
Route::middleware(['admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('logout', [AdminLoginController::class, 'destroy'])->name('logout');

    // Dashboard
    Route::get('', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index']);

    // Manage Orders
    Route::get('customers', [\App\Http\Controllers\Admin\CustomerController::class, 'index'])->name('customers');
    Route::get('orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders');
    Route::get('orders/{id}/edit', [\App\Http\Controllers\Admin\OrderController::class, 'edit'])->name('orders.edit');
    Route::put('orders/{id}', [\App\Http\Controllers\Admin\OrderController::class, 'update'])->name('orders.update');
    Route::get('orders/{id}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
    Route::get('ordersnotprocess', [\App\Http\Controllers\Admin\ShoppingCartController::class, 'index'])->name('orders-not-process');
    Route::get('ordersnotprocess/{id}', [\App\Http\Controllers\Admin\ShoppingCartController::class, 'show'])->name('orders-not-process.show');
    Route::delete('ordersnotprocess/{id}', [\App\Http\Controllers\Admin\ShoppingCartController::class, 'destroy'])->name('orders-not-process.destroy');
    Route::get('wishlist', [\App\Http\Controllers\Admin\WishlistController::class, 'index'])->name('wishlist');
    Route::get('productrate', [\App\Http\Controllers\Admin\ProductRatingController::class, 'index'])->name('product-rate');
    Route::get('mobiledevice', [\App\Http\Controllers\Admin\MobileDeviceController::class, 'index'])->name('mobile-device');
    Route::get('returnrequest', [\App\Http\Controllers\Admin\ReturnRequestController::class, 'index'])->name('return-request');

    // Manage Products
    Route::get('category', [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('category');
    Route::get('category/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'show'])->name('category.show');
    Route::get('category/{id}/edit', [\App\Http\Controllers\Admin\CategoryController::class, 'edit'])->name('category.edit');
    Route::put('category/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('category.update');
    Route::get('occassion', [\App\Http\Controllers\Admin\OccassionController::class, 'index'])->name('occassion');
    Route::get('products', [\App\Http\Controllers\Admin\ProductController::class, 'index'])->name('products');
    Route::get('giftproducts', [\App\Http\Controllers\Admin\GiftProductController::class, 'index'])->name('gift-products');

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
    Route::get('colors', [\App\Http\Controllers\Admin\ColorController::class, 'index'])->name('colors');
    Route::get('areas', [\App\Http\Controllers\Admin\AreaController::class, 'index'])->name('areas');
    Route::get('countries', [\App\Http\Controllers\Admin\CountryController::class, 'index'])->name('countries');
    Route::get('sizes', [\App\Http\Controllers\Admin\SizeController::class, 'index'])->name('sizes');
    Route::get('couponcode', [\App\Http\Controllers\Admin\CouponCodeController::class, 'index'])->name('coupon-code');
    Route::get('discounts', [\App\Http\Controllers\Admin\DiscountController::class, 'index'])->name('discounts');
    Route::get('messages', [\App\Http\Controllers\Admin\GiftMessageController::class, 'index'])->name('messages');

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
});