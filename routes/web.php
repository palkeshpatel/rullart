<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Redirect;
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

Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    return Redirect::to('/')->with('success', 'Cache cleared successfully!');
})->name('clear-cache');
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
