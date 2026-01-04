<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
| API endpoints match the CodeIgniter API structure from /application/controllers/api4/
|
*/

// API routes with locale prefix (en/ar)
Route::prefix('{locale}/api')->where(['locale' => 'en|ar'])->middleware(['locale', 'currency'])->group(function () {

    // Home API
    Route::get('home/get', [App\Http\Controllers\Api\HomeController::class, 'get']);
    Route::get('home/shopby', [App\Http\Controllers\Api\HomeController::class, 'shopby']);

    // Category API
    Route::get('category/data', [App\Http\Controllers\Api\CategoryController::class, 'data']);

    // Product API
    Route::get('product/{product}', [App\Http\Controllers\Api\ProductController::class, 'index']);

    // Shopping Cart API
    Route::post('shoppingcart/get', [App\Http\Controllers\Api\ShoppingcartController::class, 'get']);
    Route::post('shoppingcart/add', [App\Http\Controllers\Api\ShoppingcartController::class, 'add']);
    Route::post('shoppingcart/update', [App\Http\Controllers\Api\ShoppingcartController::class, 'update']);
    Route::post('shoppingcart/delete', [App\Http\Controllers\Api\ShoppingcartController::class, 'delete']);
    Route::post('shoppingcart/clear', [App\Http\Controllers\Api\ShoppingcartController::class, 'clear']);

    // Customer API
    Route::get('customer/get_by_id', [App\Http\Controllers\Api\CustomerController::class, 'getById']);
    Route::post('customer/login', [App\Http\Controllers\Api\CustomerController::class, 'login']);
    Route::post('customer/register', [App\Http\Controllers\Api\CustomerController::class, 'register']);
    Route::post('customer/update', [App\Http\Controllers\Api\CustomerController::class, 'update']);
    Route::post('customer/forgot_password', [App\Http\Controllers\Api\CustomerController::class, 'forgotPassword']);

    // Address Book API
    Route::get('addressbook/get', [App\Http\Controllers\Api\AddressbookController::class, 'get']);
    Route::post('addressbook/add', [App\Http\Controllers\Api\AddressbookController::class, 'add']);
    Route::post('addressbook/update', [App\Http\Controllers\Api\AddressbookController::class, 'update']);
    Route::post('addressbook/delete', [App\Http\Controllers\Api\AddressbookController::class, 'delete']);

    // Areas API
    Route::get('areas/get', [App\Http\Controllers\Api\AreasController::class, 'get']);

    // Checkout API
    Route::post('checkout/process', [App\Http\Controllers\Api\CheckoutController::class, 'process']);

    // My Orders API
    Route::get('myorders/get', [App\Http\Controllers\Api\MyordersController::class, 'get']);
    Route::get('myorders/get_by_id', [App\Http\Controllers\Api\MyordersController::class, 'getById']);

    // My Profile API
    Route::get('myprofile/get', [App\Http\Controllers\Api\MyprofileController::class, 'get']);
    Route::post('myprofile/update', [App\Http\Controllers\Api\MyprofileController::class, 'update']);

    // My Addresses API
    Route::get('myaddresses/get', [App\Http\Controllers\Api\MyaddressesController::class, 'get']);

    // Search API
    Route::get('search/data', [App\Http\Controllers\Api\SearchController::class, 'data']);

    // Wishlist API
    Route::get('wishlist/get', [App\Http\Controllers\Api\WishlistController::class, 'get']);
    Route::post('wishlist/add', [App\Http\Controllers\Api\WishlistController::class, 'add']);
    Route::post('wishlist/delete', [App\Http\Controllers\Api\WishlistController::class, 'delete']);

    // Occasion API
    Route::get('occassion/get', [App\Http\Controllers\Api\OccassionController::class, 'get']);

    // Payment API
    Route::post('payment/process', [App\Http\Controllers\Api\PaymentController::class, 'process']);

    // Order Complete API
    Route::post('ordercomplete/process', [App\Http\Controllers\Api\OrdercompleteController::class, 'process']);

    // Thank You API
    Route::get('thankyou/get', [App\Http\Controllers\Api\ThankyouController::class, 'get']);

    // Page API
    Route::get('page/get', [App\Http\Controllers\Api\PageController::class, 'get']);

    // Gift Items API
    Route::get('giftitems/get', [App\Http\Controllers\Api\GiftitemsController::class, 'get']);

    // Gift Titles API
    Route::get('gifttitles/get', [App\Http\Controllers\Api\GifttitlesController::class, 'get']);

    // Product Rate API
    Route::post('productrate/add', [App\Http\Controllers\Api\ProductrateController::class, 'add']);

    // Device API
    Route::post('device/register', [App\Http\Controllers\Api\DeviceController::class, 'register']);

    // Autocomplete API
    Route::get('autocomplete/get', [App\Http\Controllers\Api\AutocompleteController::class, 'get']);

    // Avenue API
    Route::get('avenue/get', [App\Http\Controllers\Api\AvenueController::class, 'get']);

    // Tabby Pay API
    Route::post('tabbypay/process', [App\Http\Controllers\Api\TabbypayController::class, 'process']);
});
