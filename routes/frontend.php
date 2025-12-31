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
use App\Http\Controllers\Frontend\MyOrdersController;
use App\Http\Controllers\Frontend\MyProfileController;
use App\Http\Controllers\Frontend\MyAddressesController;

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
    Route::get('/login/register', [LoginController::class, 'register'])->name('login.register');
    Route::post('/login/registration', [LoginController::class, 'registration'])->name('login.registration');
    Route::get('/login/forgot', function () {
        // TODO: Implement forgot password
        abort(404, 'Forgot password not implemented yet');
    })->name('login.forgot');
    
    // Social login routes (redirects - inside locale group)
    Route::get('/login/facebook', [LoginController::class, 'redirectToFacebook'])->name('login.facebook');
    Route::get('/login/google_login', [LoginController::class, 'redirectToGoogle'])->name('login.google');

    Route::get('/login/logout', [LoginController::class, 'logout'])->name('login.logout');

    // User account routes
    Route::get('/myorders', [MyOrdersController::class, 'index'])->name('myorders');
    
    Route::get('/myprofile', [MyProfileController::class, 'index'])->name('myprofile');
    Route::post('/myprofile/profile_update', [MyProfileController::class, 'profileUpdate'])->name('myprofile.update');
    Route::post('/myprofile/change_password', [MyProfileController::class, 'changePassword'])->name('myprofile.changePassword');
    
    Route::get('/myaddresses', [MyAddressesController::class, 'index'])->name('myaddresses');
    Route::post('/myaddresses/remove', [MyAddressesController::class, 'remove'])->name('myaddresses.remove');
    
    // Placeholder routes (to be implemented)
    Route::get('/orderdetails/{orderno}', function () {
        abort(404, 'Not implemented yet');
    })->name('orderdetails');

    // Shopping routes
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
    Route::post('/wishlist/add', [WishlistController::class, 'add'])->name('wishlist.add');
    Route::post('/wishlist/remove', [WishlistController::class, 'remove'])->name('wishlist.remove');

    Route::get('/shoppingcart', [ShoppingCartController::class, 'index'])->name('cart.index');
    Route::post('/shoppingcart/ajax_cart', [ShoppingCartController::class, 'ajaxCart'])->name('cart.ajax');
    Route::post('/shoppingcart/ajax_wishlist', [ShoppingCartController::class, 'ajaxWishlist'])->name('cart.ajax.wishlist');

    // Checkout routes
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/delivery', [CheckoutController::class, 'delivery'])->name('checkout.delivery');
    Route::post('/checkout/apply', [CheckoutController::class, 'applyCoupon'])->name('checkout.apply');
    Route::post('/checkout/couponremove', [CheckoutController::class, 'removeCoupon'])->name('checkout.couponremove');
    Route::post('/checkout/shippingmethod', [CheckoutController::class, 'shippingMethod'])->name('checkout.shippingmethod');
    Route::post('/checkout/process', [CheckoutController::class, 'processPayment'])->name('checkout.process');

    // Payment routes
    Route::get('/payment', [\App\Http\Controllers\Frontend\PaymentController::class, 'index'])->name('payment.index');
    
    // Thankyou/Process route (matches CI project - JavaScript calls thankyou/process)
    Route::post('/thankyou/process', [CheckoutController::class, 'processPayment'])->name('thankyou.process');

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

// Social login callback routes (outside locale group - OAuth providers redirect to absolute URLs)
Route::get('/login/facebook/callback', [LoginController::class, 'handleFacebookCallback'])->name('login.facebook.callback');
Route::get('/login/google/callback', [LoginController::class, 'handleGoogleCallback'])->name('login.google.callback');
