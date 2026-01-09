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
    Route::post('change-password', [AdminLoginController::class, 'changePassword'])->name('change-password');

    // Dashboard
    Route::get('', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index']);

    // Manage Orders
    Route::get('customers', [\App\Http\Controllers\Admin\CustomerController::class, 'index'])->name('customers');
    Route::get('customers/export', [\App\Http\Controllers\Admin\CustomerController::class, 'export'])->name('customers.export');
    Route::get('customers/create', [\App\Http\Controllers\Admin\CustomerController::class, 'create'])->name('customers.create');
    Route::post('customers', [\App\Http\Controllers\Admin\CustomerController::class, 'store'])->name('customers.store');
    Route::get('customers/{id}', [\App\Http\Controllers\Admin\CustomerController::class, 'show'])->name('customers.show');
    Route::get('customers/{id}/edit', [\App\Http\Controllers\Admin\CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('customers/{id}', [\App\Http\Controllers\Admin\CustomerController::class, 'update'])->name('customers.update');
    Route::get('orders', [\App\Http\Controllers\Admin\OrderController::class, 'index'])->name('orders');
    Route::get('orders/export', [\App\Http\Controllers\Admin\OrderController::class, 'export'])->name('orders.export');
    Route::get('orders/{id}/edit', [\App\Http\Controllers\Admin\OrderController::class, 'edit'])->name('orders.edit');
    Route::put('orders/{id}', [\App\Http\Controllers\Admin\OrderController::class, 'update'])->name('orders.update');
    Route::get('orders/{id}', [\App\Http\Controllers\Admin\OrderController::class, 'show'])->name('orders.show');
    Route::get('ordersnotprocess', [\App\Http\Controllers\Admin\ShoppingCartController::class, 'index'])->name('orders-not-process');
    Route::get('ordersnotprocess/export', [\App\Http\Controllers\Admin\ShoppingCartController::class, 'export'])->name('orders-not-process.export');
    Route::get('ordersnotprocess/{id}', [\App\Http\Controllers\Admin\ShoppingCartController::class, 'show'])->name('orders-not-process.show');
    Route::delete('ordersnotprocess/{id}', [\App\Http\Controllers\Admin\ShoppingCartController::class, 'destroy'])->name('orders-not-process.destroy');
    Route::get('wishlist', [\App\Http\Controllers\Admin\WishlistController::class, 'index'])->name('wishlist');
    Route::get('wishlist/export', [\App\Http\Controllers\Admin\WishlistController::class, 'export'])->name('wishlist.export');
    Route::get('productrate', [\App\Http\Controllers\Admin\ProductRatingController::class, 'index'])->name('product-rate');
    Route::get('productrate/export', [\App\Http\Controllers\Admin\ProductRatingController::class, 'export'])->name('product-rate.export');
    Route::get('productrate/{id}/edit', [\App\Http\Controllers\Admin\ProductRatingController::class, 'edit'])->name('product-rate.edit');
    Route::put('productrate/{id}', [\App\Http\Controllers\Admin\ProductRatingController::class, 'update'])->name('product-rate.update');
    Route::delete('productrate/{id}', [\App\Http\Controllers\Admin\ProductRatingController::class, 'destroy'])->name('product-rate.destroy');
    Route::get('mobiledevice', [\App\Http\Controllers\Admin\MobileDeviceController::class, 'index'])->name('mobile-device');
    Route::get('mobiledevice/export', [\App\Http\Controllers\Admin\MobileDeviceController::class, 'export'])->name('mobile-device.export');
    Route::get('mobiledevice/devices', [\App\Http\Controllers\Admin\MobileDeviceController::class, 'getDevices'])->name('mobile-device.devices');
    Route::post('mobiledevice/send-notification', [\App\Http\Controllers\Admin\MobileDeviceController::class, 'sendNotification'])->name('mobile-device.send-notification');
    Route::get('returnrequest', [\App\Http\Controllers\Admin\ReturnRequestController::class, 'index'])->name('return-request');
    Route::get('returnrequest/export', [\App\Http\Controllers\Admin\ReturnRequestController::class, 'export'])->name('return-request.export');
    Route::delete('returnrequest/{id}', [\App\Http\Controllers\Admin\ReturnRequestController::class, 'destroy'])->name('return-request.destroy');

    // Manage Products
    Route::get('category', [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('category');
    Route::get('category/create', [\App\Http\Controllers\Admin\CategoryController::class, 'create'])->name('category.create');
    Route::post('category', [\App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('category.store');
    Route::get('category/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'show'])->name('category.show');
    Route::get('category/{id}/edit', [\App\Http\Controllers\Admin\CategoryController::class, 'edit'])->name('category.edit');
    Route::put('category/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('category.update');
    Route::post('category/{id}/remove-image', [\App\Http\Controllers\Admin\CategoryController::class, 'removeImage'])->name('category.remove-image');
    Route::delete('category/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('category.destroy');
    Route::get('occassion', [\App\Http\Controllers\Admin\OccassionController::class, 'index'])->name('occassion');
    Route::get('occassion/create', [\App\Http\Controllers\Admin\OccassionController::class, 'create'])->name('occassion.create');
    Route::post('occassion', [\App\Http\Controllers\Admin\OccassionController::class, 'store'])->name('occassion.store');
    Route::get('occassion/{id}', [\App\Http\Controllers\Admin\OccassionController::class, 'show'])->name('occassion.show');
    Route::get('occassion/{id}/edit', [\App\Http\Controllers\Admin\OccassionController::class, 'edit'])->name('occassion.edit');
    Route::put('occassion/{id}', [\App\Http\Controllers\Admin\OccassionController::class, 'update'])->name('occassion.update');
    Route::post('occassion/{id}/remove-image', [\App\Http\Controllers\Admin\OccassionController::class, 'removeImage'])->name('occassion.remove-image');
    Route::delete('occassion/{id}', [\App\Http\Controllers\Admin\OccassionController::class, 'destroy'])->name('occassion.destroy');
    Route::get('products', [\App\Http\Controllers\Admin\ProductController::class, 'index'])->name('products');
    Route::get('products/create', [\App\Http\Controllers\Admin\ProductController::class, 'create'])->name('products.create');
    Route::post('products', [\App\Http\Controllers\Admin\ProductController::class, 'store'])->name('products.store');
    Route::get('products/subcategories', [\App\Http\Controllers\Admin\ProductController::class, 'getSubcategories'])->name('products.subcategories');
    Route::get('products/{id}', [\App\Http\Controllers\Admin\ProductController::class, 'show'])->name('products.show');
    Route::get('products/{id}/edit', [\App\Http\Controllers\Admin\ProductController::class, 'edit'])->name('products.edit');
    Route::put('products/{id}', [\App\Http\Controllers\Admin\ProductController::class, 'update'])->name('products.update');
    Route::post('products/{id}/remove-image', [\App\Http\Controllers\Admin\ProductController::class, 'removeImage'])->name('products.remove-image');
    Route::delete('products/{id}', [\App\Http\Controllers\Admin\ProductController::class, 'destroy'])->name('products.destroy');
    Route::get('giftproducts', [\App\Http\Controllers\Admin\GiftProductController::class, 'index'])->name('gift-products');
    Route::get('giftproducts/create', [\App\Http\Controllers\Admin\GiftProductController::class, 'create'])->name('gift-products.create');
    Route::post('giftproducts', [\App\Http\Controllers\Admin\GiftProductController::class, 'store'])->name('gift-products.store');
    Route::get('giftproducts/{id}', [\App\Http\Controllers\Admin\GiftProductController::class, 'show'])->name('gift-products.show');
    Route::get('giftproducts/{id}/edit', [\App\Http\Controllers\Admin\GiftProductController::class, 'edit'])->name('gift-products.edit');
    Route::put('giftproducts/{id}', [\App\Http\Controllers\Admin\GiftProductController::class, 'update'])->name('gift-products.update');
    Route::post('giftproducts/{id}/remove-image', [\App\Http\Controllers\Admin\GiftProductController::class, 'removeImage'])->name('gift-products.remove-image');
    Route::delete('giftproducts/{id}', [\App\Http\Controllers\Admin\GiftProductController::class, 'destroy'])->name('gift-products.destroy');
    Route::get('giftproducts4', [\App\Http\Controllers\Admin\GiftProduct4Controller::class, 'index'])->name('gift-products4');
    Route::get('giftproducts4/create', [\App\Http\Controllers\Admin\GiftProduct4Controller::class, 'create'])->name('gift-products4.create');
    Route::post('giftproducts4', [\App\Http\Controllers\Admin\GiftProduct4Controller::class, 'store'])->name('gift-products4.store');
    Route::get('giftproducts4/{id}', [\App\Http\Controllers\Admin\GiftProduct4Controller::class, 'show'])->name('gift-products4.show');
    Route::get('giftproducts4/{id}/edit', [\App\Http\Controllers\Admin\GiftProduct4Controller::class, 'edit'])->name('gift-products4.edit');
    Route::put('giftproducts4/{id}', [\App\Http\Controllers\Admin\GiftProduct4Controller::class, 'update'])->name('gift-products4.update');
    Route::post('giftproducts4/{id}/remove-image', [\App\Http\Controllers\Admin\GiftProduct4Controller::class, 'removeImage'])->name('gift-products4.remove-image');
    Route::delete('giftproducts4/{id}', [\App\Http\Controllers\Admin\GiftProduct4Controller::class, 'destroy'])->name('gift-products4.destroy');

    // Reports
    Route::get('sales-report-date', [\App\Http\Controllers\Admin\Reports\SalesReportController::class, 'datewise'])->name('sales-report-date');
    Route::get('sales-report-date/export/{format}', [\App\Http\Controllers\Admin\Reports\SalesReportController::class, 'exportDatewise'])->name('sales-report-date.export');
    Route::get('sales-report-date/print', [\App\Http\Controllers\Admin\Reports\SalesReportController::class, 'printDatewise'])->name('sales-report-date.print');
    Route::get('sales-report-month', [\App\Http\Controllers\Admin\Reports\SalesReportController::class, 'monthwise'])->name('sales-report-month');
    Route::get('sales-report-month/export/{format}', [\App\Http\Controllers\Admin\Reports\SalesReportController::class, 'exportMonthwise'])->name('sales-report-month.export');
    Route::get('sales-report-month/print', [\App\Http\Controllers\Admin\Reports\SalesReportController::class, 'printMonthwise'])->name('sales-report-month.print');
    Route::get('sales-report-year', [\App\Http\Controllers\Admin\Reports\SalesReportController::class, 'yearwise'])->name('sales-report-year');
    Route::get('sales-report-year/export/{format}', [\App\Http\Controllers\Admin\Reports\SalesReportController::class, 'exportYearwise'])->name('sales-report-year.export');
    Route::get('sales-report-year/print', [\App\Http\Controllers\Admin\Reports\SalesReportController::class, 'printYearwise'])->name('sales-report-year.print');
    Route::get('sales-report-customer', [\App\Http\Controllers\Admin\Reports\SalesReportController::class, 'customerwise'])->name('sales-report-customer');
    Route::get('sales-report-customer/export/{format}', [\App\Http\Controllers\Admin\Reports\SalesReportController::class, 'exportCustomerwise'])->name('sales-report-customer.export');
    Route::get('sales-report-customer/print', [\App\Http\Controllers\Admin\Reports\SalesReportController::class, 'printCustomerwise'])->name('sales-report-customer.print');
    Route::get('top-product-month', [\App\Http\Controllers\Admin\Reports\SalesReportController::class, 'topProductMonth'])->name('top-product-month');
    Route::get('top-product-month/export/{format}', [\App\Http\Controllers\Admin\Reports\SalesReportController::class, 'exportTopProductMonth'])->name('top-product-month.export');
    Route::get('top-product-month/print', [\App\Http\Controllers\Admin\Reports\SalesReportController::class, 'printTopProductMonth'])->name('top-product-month.print');
    Route::get('top-product-rate', [\App\Http\Controllers\Admin\Reports\SalesReportController::class, 'topProductRate'])->name('top-product-rate');
    Route::get('top-product-rate/export/{format}', [\App\Http\Controllers\Admin\Reports\SalesReportController::class, 'exportTopProductRate'])->name('top-product-rate.export');
    Route::get('top-product-rate/print', [\App\Http\Controllers\Admin\Reports\SalesReportController::class, 'printTopProductRate'])->name('top-product-rate.print');

    // Masters Routes
    Route::get('colors', [\App\Http\Controllers\Admin\ColorController::class, 'index'])->name('colors');
    Route::get('colors/create', [\App\Http\Controllers\Admin\ColorController::class, 'create'])->name('colors.create');
    Route::post('colors', [\App\Http\Controllers\Admin\ColorController::class, 'store'])->name('colors.store');
    Route::get('colors/{id}', [\App\Http\Controllers\Admin\ColorController::class, 'show'])->name('colors.show');
    Route::get('colors/{id}/edit', [\App\Http\Controllers\Admin\ColorController::class, 'edit'])->name('colors.edit');
    Route::put('colors/{id}', [\App\Http\Controllers\Admin\ColorController::class, 'update'])->name('colors.update');
    Route::delete('colors/{id}', [\App\Http\Controllers\Admin\ColorController::class, 'destroy'])->name('colors.destroy');
    
    Route::get('areas', [\App\Http\Controllers\Admin\AreaController::class, 'index'])->name('areas');
    Route::get('areas/create', [\App\Http\Controllers\Admin\AreaController::class, 'create'])->name('areas.create');
    Route::post('areas', [\App\Http\Controllers\Admin\AreaController::class, 'store'])->name('areas.store');
    Route::get('areas/{id}', [\App\Http\Controllers\Admin\AreaController::class, 'show'])->name('areas.show');
    Route::get('areas/{id}/edit', [\App\Http\Controllers\Admin\AreaController::class, 'edit'])->name('areas.edit');
    Route::put('areas/{id}', [\App\Http\Controllers\Admin\AreaController::class, 'update'])->name('areas.update');
    Route::delete('areas/{id}', [\App\Http\Controllers\Admin\AreaController::class, 'destroy'])->name('areas.destroy');
    
    Route::get('sizes', [\App\Http\Controllers\Admin\SizeController::class, 'index'])->name('sizes');
    Route::get('sizes/create', [\App\Http\Controllers\Admin\SizeController::class, 'create'])->name('sizes.create');
    Route::post('sizes', [\App\Http\Controllers\Admin\SizeController::class, 'store'])->name('sizes.store');
    Route::get('sizes/{id}', [\App\Http\Controllers\Admin\SizeController::class, 'show'])->name('sizes.show');
    Route::get('sizes/{id}/edit', [\App\Http\Controllers\Admin\SizeController::class, 'edit'])->name('sizes.edit');
    Route::put('sizes/{id}', [\App\Http\Controllers\Admin\SizeController::class, 'update'])->name('sizes.update');
    Route::delete('sizes/{id}', [\App\Http\Controllers\Admin\SizeController::class, 'destroy'])->name('sizes.destroy');
    
    Route::get('countries', [\App\Http\Controllers\Admin\CountryController::class, 'index'])->name('countries');
    Route::get('countries/create', [\App\Http\Controllers\Admin\CountryController::class, 'create'])->name('countries.create');
    Route::post('countries', [\App\Http\Controllers\Admin\CountryController::class, 'store'])->name('countries.store');
    Route::post('countries/update-currency-rate', [\App\Http\Controllers\Admin\CountryController::class, 'updateCurrencyRate'])->name('countries.update-currency-rate');
    Route::get('countries/{id}', [\App\Http\Controllers\Admin\CountryController::class, 'show'])->name('countries.show');
    Route::get('countries/{id}/edit', [\App\Http\Controllers\Admin\CountryController::class, 'edit'])->name('countries.edit');
    Route::put('countries/{id}', [\App\Http\Controllers\Admin\CountryController::class, 'update'])->name('countries.update');
    Route::delete('countries/{id}', [\App\Http\Controllers\Admin\CountryController::class, 'destroy'])->name('countries.destroy');
    
    Route::get('coupon-code', [\App\Http\Controllers\Admin\CouponCodeController::class, 'index'])->name('coupon-code');
    Route::get('coupon-code/create', [\App\Http\Controllers\Admin\CouponCodeController::class, 'create'])->name('coupon-code.create');
    Route::post('coupon-code', [\App\Http\Controllers\Admin\CouponCodeController::class, 'store'])->name('coupon-code.store');
    Route::get('coupon-code/{id}', [\App\Http\Controllers\Admin\CouponCodeController::class, 'show'])->name('coupon-code.show');
    Route::get('coupon-code/{id}/edit', [\App\Http\Controllers\Admin\CouponCodeController::class, 'edit'])->name('coupon-code.edit');
    Route::put('coupon-code/{id}', [\App\Http\Controllers\Admin\CouponCodeController::class, 'update'])->name('coupon-code.update');
    Route::delete('coupon-code/{id}', [\App\Http\Controllers\Admin\CouponCodeController::class, 'destroy'])->name('coupon-code.destroy');
    
    Route::get('discounts', [\App\Http\Controllers\Admin\DiscountController::class, 'index'])->name('discounts');
    Route::post('discounts', [\App\Http\Controllers\Admin\DiscountController::class, 'store'])->name('discounts.store');
    
    Route::get('messages', [\App\Http\Controllers\Admin\GiftMessageController::class, 'index'])->name('messages');
    Route::get('messages/create', [\App\Http\Controllers\Admin\GiftMessageController::class, 'create'])->name('messages.create');
    Route::post('messages', [\App\Http\Controllers\Admin\GiftMessageController::class, 'store'])->name('messages.store');
    Route::get('messages/{id}', [\App\Http\Controllers\Admin\GiftMessageController::class, 'show'])->name('messages.show');
    Route::get('messages/{id}/edit', [\App\Http\Controllers\Admin\GiftMessageController::class, 'edit'])->name('messages.edit');
    Route::put('messages/{id}', [\App\Http\Controllers\Admin\GiftMessageController::class, 'update'])->name('messages.update');
    Route::delete('messages/{id}', [\App\Http\Controllers\Admin\GiftMessageController::class, 'destroy'])->name('messages.destroy');
    
    Route::get('courier-company', [\App\Http\Controllers\Admin\CourierCompanyController::class, 'index'])->name('courier-company');
    Route::get('courier-company/create', [\App\Http\Controllers\Admin\CourierCompanyController::class, 'create'])->name('courier-company.create');
    Route::post('courier-company', [\App\Http\Controllers\Admin\CourierCompanyController::class, 'store'])->name('courier-company.store');
    Route::get('courier-company/{id}', [\App\Http\Controllers\Admin\CourierCompanyController::class, 'show'])->name('courier-company.show');
    Route::get('courier-company/{id}/edit', [\App\Http\Controllers\Admin\CourierCompanyController::class, 'edit'])->name('courier-company.edit');
    Route::put('courier-company/{id}', [\App\Http\Controllers\Admin\CourierCompanyController::class, 'update'])->name('courier-company.update');
    Route::delete('courier-company/{id}', [\App\Http\Controllers\Admin\CourierCompanyController::class, 'destroy'])->name('courier-company.destroy');

    // Manage Pages
    Route::get('homegallery', [\App\Http\Controllers\Admin\HomeGalleryController::class, 'index'])->name('home-gallery');
    Route::get('homegallery/create', [\App\Http\Controllers\Admin\HomeGalleryController::class, 'create'])->name('home-gallery.create');
    Route::post('homegallery', [\App\Http\Controllers\Admin\HomeGalleryController::class, 'store'])->name('home-gallery.store');
    Route::get('homegallery/{id}', [\App\Http\Controllers\Admin\HomeGalleryController::class, 'show'])->name('home-gallery.show');
    Route::get('homegallery/{id}/edit', [\App\Http\Controllers\Admin\HomeGalleryController::class, 'edit'])->name('home-gallery.edit');
    Route::put('homegallery/{id}', [\App\Http\Controllers\Admin\HomeGalleryController::class, 'update'])->name('home-gallery.update');
    Route::post('homegallery/{id}/remove-image', [\App\Http\Controllers\Admin\HomeGalleryController::class, 'removeImage'])->name('home-gallery.remove-image');
    Route::delete('homegallery/{id}', [\App\Http\Controllers\Admin\HomeGalleryController::class, 'destroy'])->name('home-gallery.destroy');
    Route::get('pages/home', [\App\Http\Controllers\Admin\PageController::class, 'edit'])->defaults('pagename', 'home')->name('pages.home');
    Route::put('pages/home', [\App\Http\Controllers\Admin\PageController::class, 'update'])->defaults('pagename', 'home')->name('pages.home.update');
    Route::get('pages/aboutus', [\App\Http\Controllers\Admin\PageController::class, 'edit'])->defaults('pagename', 'aboutus')->name('pages.aboutus');
    Route::put('pages/aboutus', [\App\Http\Controllers\Admin\PageController::class, 'update'])->defaults('pagename', 'aboutus')->name('pages.aboutus.update');
    Route::get('pages/corporate-gift', [\App\Http\Controllers\Admin\PageController::class, 'edit'])->defaults('pagename', 'corporate-gift')->name('pages.corporate-gift');
    Route::put('pages/corporate-gift', [\App\Http\Controllers\Admin\PageController::class, 'update'])->defaults('pagename', 'corporate-gift')->name('pages.corporate-gift.update');
    Route::get('pages/franchises', [\App\Http\Controllers\Admin\PageController::class, 'edit'])->defaults('pagename', 'franchises')->name('pages.franchises');
    Route::put('pages/franchises', [\App\Http\Controllers\Admin\PageController::class, 'update'])->defaults('pagename', 'franchises')->name('pages.franchises.update');
    Route::get('pages/contactus', [\App\Http\Controllers\Admin\PageController::class, 'edit'])->defaults('pagename', 'contactus')->name('pages.contactus');
    Route::put('pages/contactus', [\App\Http\Controllers\Admin\PageController::class, 'update'])->defaults('pagename', 'contactus')->name('pages.contactus.update');
    Route::get('pages/shipping', [\App\Http\Controllers\Admin\PageController::class, 'edit'])->defaults('pagename', 'shipping')->name('pages.shipping');
    Route::put('pages/shipping', [\App\Http\Controllers\Admin\PageController::class, 'update'])->defaults('pagename', 'shipping')->name('pages.shipping.update');
    Route::get('pages/terms', [\App\Http\Controllers\Admin\PageController::class, 'edit'])->defaults('pagename', 'terms')->name('pages.terms');
    Route::put('pages/terms', [\App\Http\Controllers\Admin\PageController::class, 'update'])->defaults('pagename', 'terms')->name('pages.terms.update');
    Route::get('pages/newsletter', [\App\Http\Controllers\Admin\PageController::class, 'edit'])->defaults('pagename', 'newsletter')->name('pages.newsletter');
    Route::put('pages/newsletter', [\App\Http\Controllers\Admin\PageController::class, 'update'])->defaults('pagename', 'newsletter')->name('pages.newsletter.update');

    // Settings
    Route::get('settings', [\App\Http\Controllers\Admin\SettingsController::class, 'index'])->name('settings');
    Route::put('settings', [\App\Http\Controllers\Admin\SettingsController::class, 'update'])->name('settings.update');
});