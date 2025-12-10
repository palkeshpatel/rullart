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
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('logout', [AdminLoginController::class, 'destroy'])->name('logout');

    // Specific admin pages
    Route::get('', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::get('dashboard', function () {
        return view('admin.dashboard');
    });

    Route::get('product', function () {
        return view('admin.product');
    })->name('product');

    Route::get('products', function () {
        return view('admin.products');
    })->name('products');

    Route::get('category', function () {
        return view('admin.category');
    })->name('category');

    Route::get('categories', function () {
        return view('admin.categories');
    })->name('categories');
});