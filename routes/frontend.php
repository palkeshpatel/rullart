<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\CategoryController;
use App\Http\Controllers\Frontend\PageController;

/*
|--------------------------------------------------------------------------
| Frontend Routes
|--------------------------------------------------------------------------
|
| Here are the frontend routes for the Rullart website. All routes support
| both language prefixes (/en/ and /ar/) and root (/) which defaults to /en/
|
*/

// Redirect root to default locale
Route::get('/', function () {
    return redirect('/en');
})->name('root');

// Language switching routes
Route::get('/language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ar'])) {
        session(['locale' => $locale]);
        App::setLocale($locale);
        
        // Get current URL and replace locale if present
        $currentUrl = request()->header('referer') ?: url('/');
        $currentUrl = str_replace('/en/', '/', $currentUrl);
        $currentUrl = str_replace('/ar/', '/', $currentUrl);
        
        // Extract path after domain
        $parsedUrl = parse_url($currentUrl);
        $path = $parsedUrl['path'] ?? '/';
        $query = !empty($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        
        // Build new URL with locale
        if ($path == '/' || $path == '') {
            return redirect('/' . $locale);
        }
        
        return redirect('/' . $locale . $path . $query);
    }
    return redirect()->back();
})->name('language.switch');

// Currency switching route
Route::get('/currency/{code}', function ($code) {
    $country = \App\Models\Country::where('currencycode', $code)
        ->where('isactive', 1)
        ->first();
    
    if ($country) {
        session([
            'currencycode' => $country->currencycode,
            'currencyrate' => $country->currencyrate,
            'currencytimestamp' => time(),
        ]);
    }
    
    return redirect()->back();
})->name('currency.switch');

// Frontend routes with optional locale prefix
Route::group(['prefix' => '{locale?}', 'where' => ['locale' => 'en|ar'], 'middleware' => ['locale', 'currency']], function () {
    
    // Homepage
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/home', [HomeController::class, 'index'])->name('home.alias');
    
    // Product routes
    Route::get('/product/{category}/{product}', [ProductController::class, 'show'])
        ->name('product.show');
    Route::get('/product/{product}', [ProductController::class, 'showByCode'])
        ->name('product.show.code');
    
    // Category routes
    Route::get('/category/{category}', [CategoryController::class, 'index'])
        ->name('category.index');
    Route::get('/all', [CategoryController::class, 'all'])->name('category.all');
    
    // Occasion routes
    Route::get('/occassion/{occassion}', [CategoryController::class, 'occassion'])
        ->name('category.occassion');
    
    // Special pages
    Route::get('/whatsnew', [CategoryController::class, 'whatsNew'])->name('whatsnew');
    Route::get('/sale', [CategoryController::class, 'sale'])->name('sale');
    
    // Static pages
    Route::get('/page/{slug}', [PageController::class, 'show'])->name('page.show');
    Route::get('/about-us', [PageController::class, 'about'])->name('about');
    Route::get('/contact', [PageController::class, 'contact'])->name('contact');
    Route::get('/shipping', [PageController::class, 'shipping'])->name('shipping');
    
    // Authentication routes
    Route::get('/login', function() {
        // TODO: Implement frontend login page
        return redirect('/admin/login'); // Temporary redirect to admin login
    })->name('frontend.login');
    
    Route::get('/login/logout', function() {
        // Match CI logout behavior - clear session and redirect
        session()->forget([
            'logged_in',
            'customerid',
            'firstname',
            'lastname',
            'email',
            'shoppingcartid',
            'wishlist_item_cnt'
        ]);
        session()->flush();
        return redirect('/' . app()->getLocale());
    })->name('login.logout');
    
    // User account routes (placeholder - implement controllers later)
    Route::get('/myorders', function() {
        abort(404, 'Not implemented yet');
    })->name('myorders');
    
    Route::get('/myprofile', function() {
        abort(404, 'Not implemented yet');
    })->name('myprofile');
    
    Route::get('/myaddresses', function() {
        abort(404, 'Not implemented yet');
    })->name('myaddresses');
    
    // Shopping routes (placeholder - implement controllers later)
    Route::get('/wishlist', function() {
        abort(404, 'Not implemented yet');
    })->name('wishlist');
    
    Route::get('/shoppingcart', function() {
        abort(404, 'Not implemented yet');
    })->name('cart.index');
});

