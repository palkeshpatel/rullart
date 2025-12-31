<?php

namespace App\Http\Controllers\Frontend;

use App\Repositories\CheckoutRepository;
use App\Repositories\AddressRepository;
use App\Repositories\ShoppingCartRepository;
use App\Services\CartCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class PaymentController extends FrontendController
{
    protected $checkoutRepository;
    protected $addressRepository;
    protected $cartRepository;
    protected $calculationService;

    public function __construct(
        CheckoutRepository $checkoutRepository,
        AddressRepository $addressRepository,
        ShoppingCartRepository $cartRepository,
        CartCalculationService $calculationService
    ) {
        parent::__construct();
        $this->checkoutRepository = $checkoutRepository;
        $this->addressRepository = $addressRepository;
        $this->cartRepository = $cartRepository;
        $this->calculationService = $calculationService;
    }

    /**
     * Display payment page
     * Matches CI Payment->index()
     */
    public function index($locale)
    {
        // Set locale if provided
        if ($locale) {
            app()->setLocale($locale);
        } else {
            $locale = app()->getLocale();
        }

        // Check if user is logged in
        if (!Session::get('logged_in')) {
            return redirect()->route('frontend.login', ['locale' => $locale]);
        }

        // Check if shipping address is set (user came from checkout)
        $shippingAddr = Session::get('shipping_addr');
        if (!$shippingAddr) {
            return redirect()->route('checkout.index', ['locale' => $locale]);
        }

        $shoppingCartId = Session::get('shoppingcartid');
        if (!$shoppingCartId) {
            return redirect()->route('cart.index', ['locale' => $locale]);
        }

        // Get detailed cart data (matches CI cart_get_items)
        $cartData = $this->cartRepository->getCartData($shoppingCartId, $locale);
        $cartItems = $cartData['shoppingcartitems'];
        
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index', ['locale' => $locale]);
        }

        // Get billing address
        $billingAddr = Session::get('billing_addr', $shippingAddr);

        // Get area names for address formatting
        $shippingAreaName = '';
        if (isset($shippingAddr['fkareaid']) && $shippingAddr['fkareaid']) {
            $shippingArea = DB::table('areamaster')->where('areaid', $shippingAddr['fkareaid'])->first();
            $shippingAreaName = $locale == 'ar' ? ($shippingArea->areanameAR ?? $shippingArea->areaname ?? '') : ($shippingArea->areaname ?? '');
        }

        $billingAreaName = '';
        if (isset($billingAddr['fkareaid']) && $billingAddr['fkareaid']) {
            $billingArea = DB::table('areamaster')->where('areaid', $billingAddr['fkareaid'])->first();
            $billingAreaName = $locale == 'ar' ? ($billingArea->areanameAR ?? $billingArea->areaname ?? '') : ($billingArea->areaname ?? '');
        }

        // Format addresses (matches CI getAddress function)
        $shippingFormatted = $this->formatAddress($shippingAddr, $shippingAreaName, $locale);
        $billingFormatted = $this->formatAddress($billingAddr, $billingAreaName, $locale);

        // Calculate item total from cart items (matches CI logic)
        $itemTotal = 0;
        $giftmessage_charge = $this->settingsArr['Gift Message Charge'] ?? 0;
        foreach ($cartItems as $item) {
            $internation_ship = $item->internation_ship ?? 1;
            $giftmessageid = $item->giftmessageid ?? 0;
            $giftqty = $item->giftqty ?? 0;
            $subtotal = $item->subtotal ?? 0;
            
            if (!empty($giftmessageid) && !empty($giftqty)) {
                $giftmessage_charge_item = $giftmessage_charge * $giftqty;
                $subtotal = $subtotal + $giftmessage_charge_item;
            }
            
            if ($internation_ship == 0 && ($shippingAddr['country'] ?? '') != config('app.default_country')) {
                // Don't add to total
            } else {
                $itemTotal = $itemTotal + $subtotal;
            }
        }
        
        // Store itemtotal in session
        Session::put('itemtotal', $itemTotal);

        // Get session totals
        $shippingCharge = Session::get('shipping_charge', 0);
        $vatPercent = Session::get('vat_percent', 0);
        $discountValue = Session::get('discountvalue', 0);
        $couponcode = Session::get('couponcode', '');
        $asGift = Session::get('asGift', '');
        $giftMessage = Session::get('giftMessage', '');

        // Calculate VAT
        $vat = 0;
        if ($vatPercent > 0) {
            $vat = (($itemTotal + $shippingCharge - $discountValue) * $vatPercent) / 100;
        }

        // Calculate cart total
        $cartTotal = $itemTotal - $discountValue + $vat + $shippingCharge;

        // Get currency code
        $currencyCode = Session::get('currencycode', config('app.default_currencycode', 'KWD'));
        $currencyRate = Session::get('currencyrate', 1);
        $defaultCountryId = config('app.default_countryid', 1);

        // Get shipping method and delivery method for terms message
        $shippingMethod = Session::get('shipping_method', 'standard');
        $deliveryMethod = Session::get('delivery_method', 'Regular Delivery');

        // Check if Tabby should be shown
        $showTabby = false;
        $allowTabby = config('app.allow_tabby', false);
        if (($currencyCode == 'KWD' || $currencyCode == 'SAR') && $allowTabby) {
            $showTabby = true;
        }

        // Get minimum order amount
        $minimumOrderAmount = $this->settingsArr['Minimum Order Amount'] ?? 0;

        $data = [
            'locale' => $locale,
            'cartItems' => $cartItems,
            'shippingAddr' => $shippingAddr,
            'billingAddr' => $billingAddr,
            'shippingFormatted' => $shippingFormatted,
            'billingFormatted' => $billingFormatted,
            'itemTotal' => $itemTotal,
            'shippingCharge' => $shippingCharge,
            'vat' => $vat,
            'vatPercent' => $vatPercent,
            'discountValue' => $discountValue,
            'cartTotal' => $cartTotal,
            'couponcode' => $couponcode,
            'asGift' => $asGift,
            'giftMessage' => $giftMessage,
            'currencyCode' => $currencyCode,
            'currencyRate' => $currencyRate,
            'defaultCountryId' => $defaultCountryId,
            'showTabby' => $showTabby,
            'shippingMethod' => $shippingMethod,
            'deliveryMethod' => $deliveryMethod,
            'minimumOrderAmount' => $minimumOrderAmount,
        ];

        return view('frontend.payment.index', $data);
    }

    /**
     * Format address for display (matches CI getAddress function)
     */
    private function formatAddress($addr, $areaname, $locale)
    {
        $formatted = ($addr['firstname'] ?? '') . ' ' . ($addr['lastname'] ?? '');

        if (isset($addr['delivery_method']) && $addr['delivery_method'] == "Avenues Mall Delivery") {
            if (!empty($addr['delivery_method'])) {
                $formatted .= "<br>" . __('Delivery Method') . " : " . __($addr['delivery_method']);
            }
            if (!empty($addr['phase'])) {
                $formatted .= "<br>" . __('Phase') . " : " . ($addr['phase'] ?? '');
            }
            if (!empty($addr['additionalinstruction'])) {
                $formatted .= "<br>" . __('Additional Instruction') . " : " . nl2br($addr['additionalinstruction']);
            }
        } else {
            if (!empty($addr['block_number'])) {
                $formatted .= "<br>" . __('Block') . " : " . $addr['block_number'];
            }
            if (!empty($addr['street_number'])) {
                $formatted .= "<br>" . __('Street') . " : " . $addr['street_number'];
            }
            if (!empty($addr['avenue_number'])) {
                $formatted .= "<br>" . __('Avenue') . " : " . $addr['avenue_number'];
            }
            if (!empty($addr['house_number'])) {
                $formatted .= "<br>" . __('House / Building') . " : " . $addr['house_number'];
            }
            if (!empty($addr['floor_number'])) {
                $formatted .= "<br>" . __('Floor Number') . " : " . $addr['floor_number'];
            }
            if (!empty($addr['flat_number'])) {
                $formatted .= "<br>" . __('Flat Number') . " : " . $addr['flat_number'];
            }
            if (!empty($addr['address'])) {
                $formatted .= "<br>" . __('Additional details') . " : " . nl2br($addr['address']);
            }
            if (($addr['country'] ?? '') == "Kuwait") {
                if ($areaname) {
                    $formatted .= "<br>" . __('Area') . " : " . $areaname;
                }
            } else {
                if (!empty($addr['city'])) {
                    $formatted .= "<br>" . __('City') . " : " . $addr['city'];
                }
            }
            $formatted .= "<br>" . __('Country') . " : " . ($addr['country'] ?? '');
        }
        if (!empty($addr['securityid'])) {
            $formatted .= "<br>" . __('Civil ID Number') . " : " . $addr['securityid'];
        }
        if (!empty($addr['mobile'])) {
            $formatted .= "<br>" . __('Tel/Mobile') . " : " . $addr['mobile'];
        }

        return $formatted;
    }
}

