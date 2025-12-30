<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Occassion;
use App\Models\Setting;
use App\Models\Country;
use App\Models\ShoppingCart;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FrontendController extends Controller
{
    protected $resourceUrl;
    protected $imageUrl;
    protected $settingsArr;
    protected $categoryMenu;
    protected $giftPackageMenu;
    protected $occassionMenu;
    protected $currencyArr;
    protected $currencyRate;
    protected $currencyCode;
    protected $locale;

    public function __construct()
    {
        // Initialize data on every request
        // This will be called when controller is instantiated
        if (app()->runningInConsole() === false) {
            $this->initializeSettings();
            $this->initializeLocale();
            $this->initializeCurrency();
            $this->initializeMenus();
            $this->shareCommonData();
        }
    }

    protected function initializeSettings()
    {
        $settings = Setting::all()->pluck('settingvalue', 'settingkey')->toArray();
        $this->settingsArr = $settings;
        $resourcePath = config('app.resource_url', '/resources/');
        // Image path should point to /storage/ for product images (not /resources/storage/)
        $imagePath = config('app.image_url', '/');

        // Ensure paths end with slash
        if (!str_ends_with($resourcePath, '/')) {
            $resourcePath .= '/';
        }
        if (!str_ends_with($imagePath, '/')) {
            $imagePath .= '/';
        }

        // Convert relative paths to full URLs
        // url() may strip trailing slash, so ensure we preserve it
        if (str_starts_with($resourcePath, 'http')) {
            $this->resourceUrl = $resourcePath;
        } else {
            $url = url($resourcePath);
            // Ensure URL ends with slash
            $this->resourceUrl = rtrim($url, '/') . '/';
        }

        if (str_starts_with($imagePath, 'http')) {
            $this->imageUrl = $imagePath;
        } else {
            $url = url($imagePath);
            // Ensure URL ends with slash
            $this->imageUrl = rtrim($url, '/') . '/';
        }
    }

    protected function initializeLocale()
    {
        $this->locale = Session::get('locale', App::getLocale() ?: 'en');

        // Validate locale
        if (!in_array($this->locale, ['en', 'ar'])) {
            $this->locale = 'en';
        }

        App::setLocale($this->locale);
        Session::put('locale', $this->locale);
    }

    protected function initializeCurrency()
    {
        // Get currency from session or detect from IP
        $this->currencyCode = Session::get('currencycode');
        $this->currencyRate = Session::get('currencyrate', 1);

        if (!$this->currencyCode) {
            // Detect country from IP or use default
            $defaultCountry = config('app.default_country', 'Kuwait');
            $defaultCurrency = config('app.default_currencycode', 'KWD');

            // Simplified IP detection - you can enhance this later
            $countryName = Session::get('ip_countryName', $defaultCountry);

            $country = Country::where('countryname', $countryName)
                ->where('isactive', 1)
                ->first();

            if ($country) {
                $this->currencyCode = $country->currencycode;
                $this->currencyRate = $country->currencyrate;
            } else {
                $this->currencyCode = $defaultCurrency;
                $this->currencyRate = 1;
            }

            Session::put('currencycode', $this->currencyCode);
            Session::put('currencyrate', $this->currencyRate);
            Session::put('currencytimestamp', time());
        } else {
            // Refresh currency rate every 10 minutes
            $timestamp = Session::get('currencytimestamp', 0);
            $mins = (time() - $timestamp) / 60;

            if ($mins > 10) {
                $country = Country::where('currencycode', $this->currencyCode)
                    ->where('isactive', 1)
                    ->first();

                if ($country) {
                    $this->currencyRate = $country->currencyrate;
                    Session::put('currencyrate', $this->currencyRate);
                    Session::put('currencytimestamp', time());
                }
            }
        }
    }

    protected function initializeMenus()
    {
        // Load category menu - match CI get_main_category()
        // CI uses parentid = 0 (not NULL) for top-level categories
        // Exclude categoryid 77 and 80 (gifting categories) from main menu, they're shown separately
        $this->categoryMenu = Category::where('ispublished', 1)
            ->where('showmenu', 1)
            ->where('parentid', 0)
            ->where('categoryid', '!=', 77)
            ->where('categoryid', '!=', 80)
            ->orderBy('displayorder', 'asc')
            ->get();

        // Load gift package categories - match CI get_gift_category() which gets categories with categoryid = 77 OR 80
        $this->giftPackageMenu = Category::where('ispublished', 1)
            ->where(function ($query) {
                $query->where('categoryid', 77)
                    ->orWhere('categoryid', 80);
            })
            ->orderBy('displayorder', 'asc')
            ->get();

        // Load occasion menu
        // Note: occassion table doesn't have displayorder column in live database
        $this->occassionMenu = Occassion::where('ispublished', 1)
            ->orderBy('occassionid', 'asc')
            ->get();

        // Load all currencies - match CI get_all_currency() which only selects currencycode
        $this->currencyArr = Country::where('isactive', 1)
            ->select('currencycode')
            ->distinct()
            ->orderBy('currencycode', 'asc')
            ->get();
    }

    protected function shareCommonData()
    {
        $cartCount = $this->getCartCount();
        $wishlistCount = $this->getWishlistCount();

        Log::info('shareCommonData: Sharing data to views', [
            'cartCount' => $cartCount,
            'wishlistCount' => $wishlistCount,
            'shoppingcartid' => Session::get('shoppingcartid'),
            'session_id' => Session::getId()
        ]);

        View::share([
            'resourceUrl' => $this->resourceUrl,
            'imageUrl' => $this->imageUrl,
            'settingsArr' => $this->settingsArr,
            'categoryMenu' => $this->categoryMenu,
            'giftPackageMenu' => $this->giftPackageMenu,
            'occassionMenu' => $this->occassionMenu,
            'currencyArr' => $this->currencyArr,
            'currencyRate' => $this->currencyRate,
            'currencyCode' => $this->currencyCode,
            'locale' => $this->locale,
            'cartCount' => $cartCount,
            'wishlistCount' => $wishlistCount,
        ]);
    }

    protected function getCartCount()
    {
        $shoppingCartId = Session::get('shoppingcartid');
        $sessionId = Session::getId();
        $customerId = Session::get('customerid', 0);

        Log::info('=== getCartCount CALLED (PAGE LOAD) ===', [
            'shoppingcartid_from_session' => $shoppingCartId,
            'session_id' => $sessionId,
            'customerid' => $customerId
        ]);

        if ($shoppingCartId) {
            $count = DB::table('shoppingcartitems')
                ->where('fkcartid', $shoppingCartId)
                ->count();

            Log::info('getCartCount: Count from shoppingcartitems', [
                'cartid' => $shoppingCartId,
                'count' => $count
            ]);

            return $count;
        }

        // If no cart ID in session, try to find cart by session ID or customer ID
        if ($customerId > 0) {
            $cart = DB::table('shoppingcartmaster')
                ->where('fkcustomerid', $customerId)
                ->orderBy('cartid', 'desc')
                ->first();

            if ($cart) {
                Log::info('getCartCount: Found cart by customer ID', [
                    'customerid' => $customerId,
                    'cartid' => $cart->cartid
                ]);

                Session::put('shoppingcartid', $cart->cartid);

                $count = DB::table('shoppingcartitems')
                    ->where('fkcartid', $cart->cartid)
                    ->count();

                Log::info('getCartCount: Count after finding cart by customer', [
                    'cartid' => $cart->cartid,
                    'count' => $count
                ]);

                return $count;
            }
        }

        // Try to find cart by session ID for guest users
        if ($sessionId) {
            // First, check if any cart exists with this session ID (with or without customerid check)
            $allCarts = DB::table('shoppingcartmaster')
                ->where('sessionid', $sessionId)
                ->orderBy('cartid', 'desc')
                ->get();

            Log::info('getCartCount: Checking carts by session ID', [
                'sessionid' => $sessionId,
                'carts_found' => $allCarts->count(),
                'carts' => $allCarts->map(function ($c) {
                    return ['cartid' => $c->cartid, 'fkcustomerid' => $c->fkcustomerid, 'sessionid' => $c->sessionid];
                })->toArray()
            ]);

            // Try to find cart with customerid = 0 first (guest cart)
            $cart = DB::table('shoppingcartmaster')
                ->where('sessionid', $sessionId)
                ->where('fkcustomerid', 0)
                ->orderBy('cartid', 'desc')
                ->first();

            if ($cart) {
                Log::info('getCartCount: Found cart by session ID (guest)', [
                    'sessionid' => $sessionId,
                    'cartid' => $cart->cartid
                ]);

                Session::put('shoppingcartid', $cart->cartid);

                $count = DB::table('shoppingcartitems')
                    ->where('fkcartid', $cart->cartid)
                    ->count();

                Log::info('getCartCount: Count after finding cart by session', [
                    'cartid' => $cart->cartid,
                    'count' => $count
                ]);

                return $count;
            } else {
                // If no guest cart, check if there's any cart with this session ID
                $anyCart = DB::table('shoppingcartmaster')
                    ->where('sessionid', $sessionId)
                    ->orderBy('cartid', 'desc')
                    ->first();

                if ($anyCart) {
                    Log::info('getCartCount: Found cart by session ID (any customer)', [
                        'sessionid' => $sessionId,
                        'cartid' => $anyCart->cartid,
                        'fkcustomerid' => $anyCart->fkcustomerid
                    ]);

                    Session::put('shoppingcartid', $anyCart->cartid);

                    $count = DB::table('shoppingcartitems')
                        ->where('fkcartid', $anyCart->cartid)
                        ->count();

                    Log::info('getCartCount: Count after finding any cart by session', [
                        'cartid' => $anyCart->cartid,
                        'count' => $count
                    ]);

                    return $count;
                }
            }
        }

        // Last resort: For guest users, try to find the most recent cart created in the last 30 minutes
        // This is a fallback in case session ID changes (shouldn't happen, but as a safety net)
        if ($customerId == 0 && $sessionId) {
            $thirtyMinutesAgo = \Carbon\Carbon::now()->subMinutes(30)->format('Y-m-d H:i:s');
            $recentCart = DB::table('shoppingcartmaster')
                ->where('fkcustomerid', 0)
                ->where('orderdate', '>=', $thirtyMinutesAgo)
                ->orderBy('cartid', 'desc')
                ->first();

            if ($recentCart) {
                Log::info('getCartCount: Found recent guest cart (fallback)', [
                    'cartid' => $recentCart->cartid,
                    'sessionid_in_db' => $recentCart->sessionid,
                    'current_sessionid' => $sessionId,
                    'orderdate' => $recentCart->orderdate
                ]);

                // Check if items exist for this cart - use fresh query (no caching)
                $count = DB::table('shoppingcartitems')
                    ->where('fkcartid', $recentCart->cartid)
                    ->count();

                // Also check ALL items with this cart ID (including any that might have been inserted just now)
                $allItemsForCart = DB::table('shoppingcartitems')
                    ->where('fkcartid', $recentCart->cartid)
                    ->get(['cartitemid', 'fkproductid', 'fkcartid', 'qty', 'size', 'createdon']);

                Log::info('getCartCount: Direct query for cart items', [
                    'cartid' => $recentCart->cartid,
                    'count' => $count,
                    'items_found' => $allItemsForCart->count(),
                    'items_detail' => $allItemsForCart->take(10)->map(function ($item) {
                        return [
                            'cartitemid' => $item->cartitemid,
                            'fkproductid' => $item->fkproductid,
                            'fkcartid' => $item->fkcartid,
                            'qty' => $item->qty,
                            'size' => $item->size,
                            'createdon' => $item->createdon
                        ];
                    })->toArray()
                ]);

                // Also check for orphaned items (fkcartid = 0) that might belong to this cart
                // Find items created around the same time as the cart (within 1 hour, expanded window)
                $cartTime = \Carbon\Carbon::parse($recentCart->orderdate);
                $oneHourAfter = $cartTime->copy()->addHours(1)->format('Y-m-d H:i:s');
                $oneHourBefore = $cartTime->copy()->subHours(1)->format('Y-m-d H:i:s');

                // Also check for items created in the last 2 hours (broader search)
                $twoHoursAgo = \Carbon\Carbon::now()->subHours(2)->format('Y-m-d H:i:s');

                $orphanedItems = DB::table('shoppingcartitems')
                    ->where('fkcartid', 0)
                    ->where(function ($query) use ($oneHourBefore, $oneHourAfter, $twoHoursAgo) {
                        $query->where(function ($q) use ($oneHourBefore, $oneHourAfter) {
                            $q->where('createdon', '>=', $oneHourBefore)
                                ->where('createdon', '<=', $oneHourAfter);
                        })
                            ->orWhere('createdon', '>=', $twoHoursAgo); // Also check recent items
                    })
                    ->orderBy('createdon', 'desc')
                    ->get(['cartitemid', 'fkproductid', 'fkcartid', 'createdon']);

                Log::info('getCartCount: Checking for orphaned items', [
                    'cartid' => $recentCart->cartid,
                    'cart_orderdate' => $recentCart->orderdate,
                    'time_window_cart' => $oneHourBefore . ' to ' . $oneHourAfter,
                    'time_window_recent' => $twoHoursAgo . ' to now',
                    'orphaned_items_found' => $orphanedItems->count(),
                    'orphaned_items_sample' => $orphanedItems->take(10)->map(function ($item) {
                        return ['cartitemid' => $item->cartitemid, 'fkproductid' => $item->fkproductid, 'fkcartid' => $item->fkcartid, 'createdon' => $item->createdon];
                    })->toArray()
                ]);

                // Fix orphaned items by updating their fkcartid
                if ($orphanedItems->count() > 0) {
                    $orphanedIds = $orphanedItems->pluck('cartitemid')->toArray();
                    DB::table('shoppingcartitems')
                        ->whereIn('cartitemid', $orphanedIds)
                        ->update(['fkcartid' => $recentCart->cartid]);

                    Log::info('getCartCount: Fixed orphaned items', [
                        'cartid' => $recentCart->cartid,
                        'items_fixed' => count($orphanedIds)
                    ]);

                    // Recalculate count after fixing
                    $count = DB::table('shoppingcartitems')
                        ->where('fkcartid', $recentCart->cartid)
                        ->count();

                    Log::info('getCartCount: Count after fixing orphaned items', [
                        'cartid' => $recentCart->cartid,
                        'new_count' => $count
                    ]);
                }

                // Update the cart's session ID to match current session (so it persists)
                // Do this even if count is 0, so the cart can be found on next request
                DB::table('shoppingcartmaster')
                    ->where('cartid', $recentCart->cartid)
                    ->update(['sessionid' => $sessionId]);

                Session::put('shoppingcartid', $recentCart->cartid);

                Log::info('getCartCount: Using recent cart and updated session ID', [
                    'cartid' => $recentCart->cartid,
                    'count' => $count
                ]);

                return $count;
            }
        }

        // Debug: Check if ANY carts exist in database (for debugging)
        $totalCarts = DB::table('shoppingcartmaster')->count();
        $guestCarts = DB::table('shoppingcartmaster')->where('fkcustomerid', 0)->count();
        $oneHourAgo = \Carbon\Carbon::now()->subHours(1)->format('Y-m-d H:i:s');
        $recentGuestCarts = DB::table('shoppingcartmaster')
            ->where('fkcustomerid', 0)
            ->where('orderdate', '>=', $oneHourAgo)
            ->count();

        Log::info('getCartCount: No cart found, returning 0', [
            'sessionid' => $sessionId,
            'customerid' => $customerId,
            'debug_total_carts' => $totalCarts,
            'debug_guest_carts' => $guestCarts,
            'debug_recent_guest_carts_1h' => $recentGuestCarts
        ]);
        return 0;
    }

    protected function getWishlistCount()
    {
        $customerId = Session::get('customerid');
        if ($customerId) {
            return DB::table('wishlist')
                ->where('fkcustomerid', $customerId)
                ->count();
        }
        // Check session wishlist
        return Session::get('wishlist_item_cnt', 0);
    }

    protected function getWhatsAppNumber()
    {
        return $this->settingsArr['WhatsApp Number'] ?? '';
    }
}
