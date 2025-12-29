<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\ProductController;
use App\Http\Controllers\Frontend\CategoryController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\SearchController;
use App\Http\Controllers\Frontend\ShoppingCartController;
use App\Http\Controllers\Frontend\CheckoutController;
use App\Http\Controllers\Frontend\WishlistController;
use App\Http\Controllers\Frontend\LoginController;

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
Route::group(['prefix' => '{locale}', 'where' => ['locale' => 'en|ar'], 'middleware' => ['locale', 'currency']], function () {

    // Homepage
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/home', [HomeController::class, 'index'])->name('home.alias');

    // Product routes
    Route::get('/product/{category}/{product}', [ProductController::class, 'show'])
        ->name('product.show');
    Route::get('/product/{product}', [ProductController::class, 'showByCode'])
        ->name('product.show.code');

    // Category routes - must come before other routes that might conflict
    Route::get('/category/{categoryCode}', [CategoryController::class, 'index'])
        ->where('categoryCode', '[a-zA-Z0-9\-]+')
        ->name('category.index');
    Route::get('/all', [CategoryController::class, 'all'])->name('category.all');

    // AJAX product listing routes (for dynamic filtering)
    Route::get('/prodlisting/category/{categoryCode}', [CategoryController::class, 'prodlisting'])
        ->where('categoryCode', '[a-zA-Z0-9\-]+')
        ->name('category.prodlisting');
    Route::get('/prodlisting/occassion/{occassion}', [CategoryController::class, 'prodlistingOccassion'])
        ->name('category.prodlisting.occassion');
    Route::get('/prodlisting/whatsnew', [CategoryController::class, 'prodlistingWhatsnew'])
        ->name('category.prodlisting.whatsnew');
    Route::get('/prodlisting/sale', [CategoryController::class, 'prodlistingSale'])
        ->name('category.prodlisting.sale');
    Route::get('/prodlisting/search', [SearchController::class, 'prodlisting'])
        ->name('search.prodlisting');

    // Occasion routes
    Route::get('/occassion/{occassion}', [CategoryController::class, 'occassion'])
        ->name('category.occassion');

    // Special pages
    Route::get('/whatsnew', [CategoryController::class, 'whatsNew'])->name('whatsnew');
    Route::get('/sale', [CategoryController::class, 'sale'])->name('sale');

    // Search route
    Route::get('/search', [SearchController::class, 'index'])->name('search');

    // Static pages
    Route::get('/page/{slug}', [PageController::class, 'show'])->name('page.show');
    Route::get('/about-us', [PageController::class, 'about'])->name('about');
    Route::get('/contact', [PageController::class, 'contact'])->name('contact');
    Route::get('/shipping', [PageController::class, 'shipping'])->name('shipping');

    // Authentication routes
    Route::get('/login', [LoginController::class, 'index'])->name('frontend.login');
    Route::post('/login/validate', [LoginController::class, 'validateLogin'])->name('login.validate');
    Route::post('/login/validate_guest', [LoginController::class, 'validateGuest'])->name('login.validate.guest');
    Route::post('/login/guest', [LoginController::class, 'guestLogin'])->name('login.guest');
    Route::get('/login/register', function () {
        // TODO: Implement registration
        abort(404, 'Registration not implemented yet');
    })->name('login.register');
    Route::get('/login/forgot', function () {
        // TODO: Implement forgot password
        abort(404, 'Forgot password not implemented yet');
    })->name('login.forgot');
    Route::get('/login/google_login', function () {
        // TODO: Implement Google login
        abort(404, 'Google login not implemented yet');
    })->name('login.google');

    Route::get('/login/logout', function () {
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
    Route::get('/myorders', function () {
        abort(404, 'Not implemented yet');
    })->name('myorders');

    Route::get('/myprofile', function () {
        abort(404, 'Not implemented yet');
    })->name('myprofile');

    Route::get('/myaddresses', function () {
        abort(404, 'Not implemented yet');
    })->name('myaddresses');

    // Shopping routes
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
    Route::post('/wishlist/add', [WishlistController::class, 'add'])->name('wishlist.add');
    Route::post('/wishlist/remove', [WishlistController::class, 'remove'])->name('wishlist.remove');

    Route::get('/shoppingcart', [ShoppingCartController::class, 'index'])->name('cart.index');
    Route::post('/shoppingcart/ajax', [ShoppingCartController::class, 'ajaxCart'])->name('cart.ajax');

    // Checkout routes
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout/process', [CheckoutController::class, 'processPayment'])->name('checkout.process');

    // Change currency route (matches CI format: changecurrency?currency=KWD)
    Route::get('/changecurrency', function (\Illuminate\Http\Request $request) {
        $currency = $request->get('currency');
        if ($currency) {
            $country = \App\Models\Country::where('currencycode', $currency)
                ->where('isactive', 1)
                ->first();

            if ($country) {
                session([
                    'currencycode' => $country->currencycode,
                    'currencyrate' => $country->currencyrate,
                    'currencytimestamp' => time(),
                ]);
            }
        }
        return redirect()->back();
    })->name('changecurrency');
});
