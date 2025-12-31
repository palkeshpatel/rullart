<?php

namespace App\Http\Controllers\Frontend;

use App\Repositories\CheckoutRepository;
use App\Repositories\AddressRepository;
use App\Repositories\ShoppingCartRepository;
use App\Services\CartCalculationService;
use App\Services\KnetPaymentService;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class CheckoutController extends FrontendController
{
    protected $checkoutRepository;
    protected $addressRepository;
    protected $calculationService;
    protected $cartRepository;

    public function __construct(
        CheckoutRepository $checkoutRepository,
        AddressRepository $addressRepository,
        CartCalculationService $calculationService,
        ShoppingCartRepository $cartRepository
    ) {
        parent::__construct();
        $this->checkoutRepository = $checkoutRepository;
        $this->addressRepository = $addressRepository;
        $this->calculationService = $calculationService;
        $this->cartRepository = $cartRepository;
    }

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
            Session::put('login_redirect', 'checkout');
            return redirect()->route('frontend.login', ['locale' => $locale]);
        }

        $shoppingCartId = Session::get('shoppingcartid');

        if (!$shoppingCartId) {
            return redirect()->route('cart.index', ['locale' => $locale]);
        }

        // Get cart items
        $cartItems = $this->checkoutRepository->getCartItems($shoppingCartId, $locale);

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index', ['locale' => $locale]);
        }

        // Get customer addresses (addressbook)
        $customerId = Session::get('customerid');
        $addresses = $this->addressRepository->getCustomerAddresses($customerId);

        // Get countries - matches CI $data["country"] = $this->country->get_all();
        $countries = Country::where('isactive', 1)->get();

        // Get shipping address from session or use first address
        $shippingAddr = Session::get('shipping_addr');
        $billingAddr = Session::get('billing_addr');
        $addressId = Session::get('addressid', 0);
        
        if (!$shippingAddr && $addresses->count() > 0) {
            $firstAddress = $addresses->first();
            $shippingAddr = [
                'firstname' => $firstAddress->firstname ?? '',
                'lastname' => $firstAddress->lastname ?? '',
                'mobile' => $firstAddress->mobile ?? '',
                'title' => $firstAddress->title ?? '',
                'country' => $firstAddress->countryname ?? config('app.default_country', 'Kuwait'),
                'fkareaid' => $firstAddress->fkareaid ?? '',
                'address' => $firstAddress->address ?? '',
                'addressid' => $firstAddress->addressid ?? 0,
                'block_number' => $firstAddress->block_number ?? '',
                'street_number' => $firstAddress->street_number ?? '',
                'house_number' => $firstAddress->house_number ?? '',
                'floor_number' => $firstAddress->floor_number ?? '',
                'flat_number' => $firstAddress->flat_number ?? '',
                'city' => $firstAddress->city ?? '',
                'delivery_method' => 'Regular Delivery',
            ];
            $billingAddr = $shippingAddr;
            $addressId = $firstAddress->addressid ?? 0;
            Session::put('shipping_addr', $shippingAddr);
            Session::put('billing_addr', $billingAddr);
            Session::put('addressid', $addressId);
            Session::put('sameAddress', 'on');
        }

        if (!$shippingAddr) {
            $shippingAddr = [
                'firstname' => Session::get('firstname', ''),
                'lastname' => Session::get('lastname', ''),
                'mobile' => '',
                'title' => '',
                'country' => config('app.default_country', 'Kuwait'),
                'fkareaid' => '',
                'address' => '',
                'addressid' => 0,
                'block_number' => '',
                'street_number' => '',
                'house_number' => '',
                'floor_number' => '',
                'flat_number' => '',
                'city' => '',
                'delivery_method' => 'Regular Delivery',
            ];
            $billingAddr = $shippingAddr;
        }

        // Get areas for shipping country
        $shippingCountry = $shippingAddr['country'] ?? config('app.default_country', 'Kuwait');
        $areas = $this->addressRepository->getAreasByCountry($shippingCountry);

        // Get shipping methods
        $shippingMethods = [
            'standard' => __('Standard Delivery (2-5 working days)'),
            'express' => __('Express Delivery (Same Day)'),
        ];

        // Get coupon code from session
        $couponcode = Session::get('couponcode', '');

        // Calculate totals using service
        $shippingCharge = Session::get('shipping_charge', 0);
        $vatPercent = Session::get('vat_percent', 0);
        $totals = $this->calculationService->calculateCartTotals($cartItems, $shippingCharge, $vatPercent);

        $data = [
            'locale' => $locale,
            'cartItems' => $cartItems,
            'addresses' => $addresses,
            'countries' => $countries,
            'areas' => $areas,
            'shippingAddr' => $shippingAddr,
            'billingAddr' => $billingAddr,
            'addressId' => $addressId,
            'sameAddress' => Session::get('sameAddress', 'on'),
            'couponcode' => $couponcode,
            'shippingMethods' => $shippingMethods,
            'totals' => [
                'subtotal' => $totals['total'],
                'shipping' => $totals['shippingCharge'],
                'vat' => $totals['vat'],
                'vatPercent' => $vatPercent,
                'total' => $totals['carttotal'],
            ],
        ];

        return view('frontend.checkout.index', $data);
    }

    /**
     * Handle delivery address form submission
     * Matches CI Checkout->delivery()
     */
    public function delivery(Request $request, $locale)
    {
        // Validation will be added based on CI rules
        // For now, basic validation
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|min:3|max:25',
            'lastname' => 'required|min:3|max:25',
            'mobile' => 'required|min:3|max:25',
            'country' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()->first()
            ], 422);
        }

        // Save address to session (will be saved to DB in payment step)
        $shippingAddr = [
            'firstname' => $request->input('firstname'),
            'lastname' => $request->input('lastname'),
            'mobile' => $request->input('mobile'),
            'country' => $request->input('country'),
            'addressTitle' => $request->input('addressTitle'),
            'area' => $request->input('area'),
            'block_number' => $request->input('block_number'),
            'street_number' => $request->input('street_number'),
            'house_number' => $request->input('house_number'),
            'floor_number' => $request->input('floor_number'),
            'flat_number' => $request->input('flat_number'),
            'city' => $request->input('city'),
            'delivery_method' => $request->input('delivery_method', 'Regular Delivery'),
        ];

        Session::put('shipping_addr', $shippingAddr);
        Session::put('shipping_country', $request->input('country'));
        
        // Store shipping method and delivery method in session
        $shippingMethod = $request->input('shipping_method', 'standard');
        Session::put('shipping_method', $shippingMethod);
        Session::put('delivery_method', $request->input('delivery_method', 'Regular Delivery'));

        // Set billing address same as shipping if checkbox is checked
        if ($request->input('sameAddress') == 'on') {
            Session::put('billing_addr', $shippingAddr);
            Session::put('sameAddress', 'on');
        }

        // Return empty error object so JS redirects to payment page
        // Matches CI: echo json_encode(array("status" => true));
        return response()->json([
            'status' => true
        ]);
    }

    /**
     * Apply coupon code
     * Matches CI Checkout->apply()
     */
    public function applyCoupon(Request $request)
    {
        $couponcode = $request->input('couponcode');
        $email = Session::get('email');

        if (empty($couponcode)) {
            return response()->json([
                'status' => false,
                'msg' => __('Please enter coupon code')
            ]);
        }

        // TODO: Implement coupon validation logic from ShoppingCartRepository
        // For now, return success
        Session::put('couponcode', $couponcode);
        
        // Get cart view for response
        $shoppingCartId = Session::get('shoppingcartid');
        $locale = app()->getLocale();
        $cartData = $this->checkoutRepository->getCartItems($shoppingCartId, $locale);
        // TODO: Generate cartview HTML

        return response()->json([
            'status' => true,
            'msg' => strtoupper($couponcode) . ' - ' . __('Code applied successfully'),
            'couponcode' => $couponcode,
            'cartview' => '' // Will be populated with cart view HTML
        ]);
    }

    /**
     * Remove coupon code
     * Matches CI Checkout->couponremove()
     */
    public function removeCoupon()
    {
        Session::put('couponcode', '');
        Session::put('couponvalue', 0);

        // Get cart view for response
        $shoppingCartId = Session::get('shoppingcartid');
        $locale = app()->getLocale();
        // TODO: Generate cartview HTML

        return response()->json([
            'status' => true,
            'msg' => __('Coupon code removed successfully'),
            'cartview' => '' // Will be populated with cart view HTML
        ]);
    }

    /**
     * Update shipping method
     * Matches CI Checkout->shippingmethod()
     */
    public function shippingMethod(Request $request)
    {
        $shippingMethod = $request->input('shipping_method');
        Session::put('shipping_method', $shippingMethod);

        // TODO: Calculate shipping charge based on country and method
        // Get cart view for response
        $shoppingCartId = Session::get('shoppingcartid');
        $locale = app()->getLocale();
        // TODO: Generate cartview HTML

        return response()->json([
            'status' => true,
            'msg' => __('Shipping method updated successfully'),
            'cartview' => '' // Will be populated with cart view HTML
        ]);
    }

    public function processPayment(Request $request)
    {
        $locale = app()->getLocale();
        
        // Check if user is logged in
        if (!Session::get('logged_in')) {
            return response()->json([
                'error' => 'Please login to continue'
            ], 401);
        }

        // Check if shipping address is set
        $shippingAddr = Session::get('shipping_addr');
        if (!$shippingAddr) {
            return response()->json([
                'error' => 'Shipping address is required'
            ], 400);
        }

        // Get payment method
        $method = $request->input('method');
        if (!$method) {
            return response()->json([
                'error' => 'Payment method is required'
            ], 400);
        }

        // Get cart data
        $shoppingCartId = Session::get('shoppingcartid');
        if (!$shoppingCartId) {
            return response()->json([
                'error' => 'Cart is empty'
            ], 400);
        }

        // Get cart items using ShoppingCartRepository
        $cartData = $this->cartRepository->getCartData($shoppingCartId, $locale);
        $cartItems = $cartData['shoppingcartitems'] ?? collect();
        
        if ($cartItems->isEmpty()) {
            return response()->json([
                'error' => (object)[],
                'redirect' => route('home', ['locale' => $locale])
            ]);
        }

        // Get session data
        $customerId = Session::get('customerid', 0);
        $itemTotal = Session::get('itemtotal', 0);
        $shippingCharge = Session::get('shipping_charge', 0);
        $discountValue = Session::get('discountvalue', 0);
        $vatPercent = Session::get('vat_percent', 0);
        $cartTotal = Session::get('carttotal', 0);
        $currencyCode = Session::get('currencycode', 'KWD');
        $currencyRate = Session::get('currencyrate', 1);
        $billingAddr = Session::get('billing_addr', $shippingAddr);
        $shippingMethod = Session::get('shipping_method', 'standard');
        $deliveryMethod = Session::get('delivery_method', 'Regular Delivery');
        $asGift = Session::get('asGift', 'off') == 'on' ? '1' : '0';
        $giftMessage = Session::get('giftMessage', '');
        $couponcode = Session::get('couponcode', '');
        $couponvalue = Session::get('couponvalue', 0);

        // Calculate VAT
        $vat = 0;
        if ($vatPercent > 0) {
            $vat = (($itemTotal + $shippingCharge - $discountValue) * $vatPercent) / 100;
        }

        // Get islive setting (0 = test, 1 = live)
        $islive = config('app.islive', 0);

        // Handle different payment methods
        if ($method == "Knet") {
            // Create Knet payment URL
            $knetService = new KnetPaymentService();
            $knetService->setOrderId($shoppingCartId)
                ->setAmount($cartTotal)
                ->setResponseUrl(url('/' . $locale . '/knetresponse'))
                ->setErrorUrl(url('/' . $locale . '/ordererror'))
                ->setUdf1(Session::get('email', ''))
                ->setUdf2($customerId)
                ->setUdf3($shoppingCartId)
                ->setUdf4($cartTotal)
                ->setLanguage($locale);
            
            $payURL = $knetService->performPayment();
            
            // Return format expected by checkout.js: {error: {}, redirect: 'url'}
            return response()->json([
                'error' => (object)[], // Empty object for $.isEmptyObject() check
                'redirect' => $payURL
            ]);
        } else if ($method == "Credit Card") {
            // Credit Card payment
            $payURL = route('thankyou.creditcard', ['locale' => $locale, 'cartid' => $shoppingCartId]);
            return response()->json([
                'error' => (object)[],
                'redirect' => $payURL
            ]);
        } else if ($method == "Apple Pay") {
            // Apple Pay
            $payURL = route('thankyou.applepay', ['locale' => $locale, 'cartid' => $shoppingCartId]);
            return response()->json([
                'error' => (object)[],
                'redirect' => $payURL
            ]);
        } else if ($method == "tabby") {
            // Tabby payment - will be handled via API
            // For now, return placeholder
            $payURL = route('thankyou.tabby', ['locale' => $locale, 'cartid' => $shoppingCartId]);
            return response()->json([
                'error' => (object)[],
                'redirect' => $payURL
            ]);
        } else {
            return response()->json([
                'error' => 'Invalid payment method'
            ], 400);
        }
    }
}
