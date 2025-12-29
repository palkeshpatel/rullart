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
        if ($shoppingCartId) {
            return DB::table('shoppingcartitem')
                ->where('fkshoppingcartid', $shoppingCartId)
                ->count();
        }
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
