<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoutingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider, and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

require __DIR__ . '/auth.php';
require __DIR__ . '/frontend.php';

// Admin routes - only apply auth middleware to admin paths
Route::group(['prefix' => 'admin', 'middleware' => 'auth'], function () {
    Route::get('dashboard', function () {
        return redirect('/admin/dashboard');
    })->name('admin.dashboard');
});

// Block old apps/ecommerce routes - redirect to admin
Route::get('apps/{any}', function () {
    return redirect('/admin/dashboard');
})->where('any', '.*');

Route::get('dashboards/{any}', function () {
    return redirect('/admin/dashboard');
})->where('any', '.*');
