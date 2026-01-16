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
        // Settings table uses 'name' and 'details' columns (not 'settingkey' and 'settingvalue')
        $settings = Setting::all()->pluck('details', 'name')->toArray();
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
        // Get locale from URL segment first (most reliable), then session, then default
        $request = request();
        $locale = $request->segment(1);

        // Validate locale from URL
        if (!in_array($locale, ['en', 'ar'])) {
            // Fallback to session or default
            $locale = Session::get('locale', 'en');
        }

        // Validate locale
        if (!in_array($locale, ['en', 'ar'])) {
            $locale = 'en';
        }

        // Set locale in application and session
        App::setLocale($locale);
        Session::put('locale', $locale);
        $this->locale = $locale;
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
        // Share static data that doesn't depend on session
        // Cart and wishlist count are shared by ShareCartCount middleware AFTER session loads
        View::share([
            'resourceUrl' => $this->resourceUrl,
            'imageUrl' => $this->imageUrl,
            'settingsArr' => $this->settingsArr,
            'categoryMenu' => $this->categoryMenu,
            'giftPackageMenu' => $this->giftPackageMenu,
            'occassionMenu' => $this->occassionMenu,
            'currencyArr' => $this->currencyArr,
            'locale' => $this->locale,
            'currencyRate' => $this->currencyRate,
            'currencyCode' => $this->currencyCode,
            // Cart and wishlist count default to 0 - will be updated by ShareCartCount middleware
            'cartCount' => 0,
            'wishlistCount' => 0,
        ]);
    }

    protected function getCartCount()
    {
        $shoppingCartId = Session::get('shoppingcartid');
        $sessionId = Session::getId();

        // Log session details for debugging
        Log::info('=== GET CART COUNT START ===', [
            'session_id' => $sessionId,
            'shoppingcartid_from_session' => $shoppingCartId,
            'session_has_data' => Session::has('shoppingcartid'),
            'session_all_keys' => array_keys(Session::all()),
        ]);

        // Try multiple ways to get customer ID
        $customerId = Session::get('customerid', 0);
        if ($customerId == 0) {
            // Try alternative session key
            $customerId = Session::get('customer_id', 0);
        }
        // Also check if user is logged in
        $isLoggedIn = Session::get('logged_in', false);
        if ($isLoggedIn && $customerId == 0) {
            // If logged in but no customer ID, try to get from session data
            $customerId = Session::get('customerid');
        }

        if ($shoppingCartId) {
            $count = DB::table('shoppingcartitems')
                ->where('fkcartid', $shoppingCartId)
                ->count();

            Log::info('Cart count found from session cartid', [
                'shoppingcartid' => $shoppingCartId,
                'count' => $count,
            ]);

            return $count;
        }

        // Use repository for cart lookup
        $cartRepository = app(\App\Repositories\ShoppingCartRepository::class);

        // If no cart ID in session, try to find cart by customer ID (for logged-in users)
        if ($customerId > 0 || $isLoggedIn) {
            // If logged in but customer ID is 0, try to find it from database
            if ($isLoggedIn && $customerId == 0) {
                // Try to get customer ID from email in session
                $email = Session::get('email');
                if ($email) {
                    $customer = DB::table('customers')
                        ->where('email', $email)
                        ->where('isactive', 1)
                        ->first();
                    if ($customer) {
                        $customerId = $customer->customerid;
                        Session::put('customerid', $customerId);
                    }
                }
            }

            if ($customerId > 0) {
                $cartId = $cartRepository->getCartIdForCustomer($customerId);

                if ($cartId) {
                    Session::put('shoppingcartid', $cartId);
                    $count = $cartRepository->getCartItemCount($cartId);
                    Log::info('Cart count found by customer ID', [
                        'customerid' => $customerId,
                        'cartid' => $cartId,
                        'count' => $count,
                    ]);
                    return $count;
                }
            }
        }

        // Try to find cart by session ID (strictly session-based - no fallback to other sessions)
        if ($sessionId) {
            $cartId = $cartRepository->getCartIdBySessionId($sessionId, $customerId);

            if ($cartId) {
                Session::put('shoppingcartid', $cartId);
                $count = $cartRepository->getCartItemCount($cartId);
                Log::info('Cart count found by session ID', [
                    'session_id' => $sessionId,
                    'cartid' => $cartId,
                    'count' => $count,
                ]);
                return $count;
            } else {
                Log::warning('No cart found by session ID', [
                    'session_id' => $sessionId,
                    'customerid' => $customerId,
                ]);
            }
        }

        // No cart found - return 0
        // REMOVED: Fallback logic that was finding carts from other sessions/devices
        // Cart must be strictly session-based to prevent showing cart count across different browsers/devices
        Log::info('=== GET CART COUNT END === No cart found, returning 0');
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
