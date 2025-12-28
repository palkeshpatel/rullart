<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class CheckoutController extends FrontendController
{
    public function index()
    {
        $locale = app()->getLocale();
        
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
        $cartItems = $this->getCartItems($shoppingCartId);
        
        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index', ['locale' => $locale]);
        }
        
        // Get customer addresses
        $customerId = Session::get('customerid');
        $addresses = DB::table('customeraddress')
            ->where('fkcustomerid', $customerId)
            ->where('isactive', 1)
            ->get();
        
        // Get areas for address form
        $areas = DB::table('areas')
            ->where('isactive', 1)
            ->orderBy('displayorder', 'asc')
            ->get();
        
        // Get shipping methods
        $shippingMethods = [
            'standard' => __('Standard Shipping'),
            'express' => __('Express Shipping'),
        ];
        
        // Calculate totals
        $totals = $this->calculateTotals($cartItems);
        
        $data = [
            'cartItems' => $cartItems,
            'addresses' => $addresses,
            'areas' => $areas,
            'shippingMethods' => $shippingMethods,
            'totals' => $totals,
        ];
        
        return view('frontend.checkout.index', $data);
    }
    
    protected function getCartItems($shoppingCartId)
    {
        return DB::table('shoppingcartitem as sci')
            ->select([
                'sci.*',
                'p.productcode',
                'p.shortdescr',
                'p.shortdescrAR',
                'p.title',
                'p.titleAR',
                'p.photo1',
                'p.price',
                'c.categorycode',
            ])
            ->leftJoin('products as p', 'sci.fkproductid', '=', 'p.productid')
            ->leftJoin('category as c', 'p.fkcategoryid', '=', 'c.categoryid')
            ->where('sci.fkshoppingcartid', $shoppingCartId)
            ->get();
    }
    
    protected function calculateTotals($cartItems)
    {
        $subtotal = 0;
        $shippingCharge = Session::get('shipping_charge', 0);
        $vatPercent = Session::get('vat_percent', 0);
        
        foreach ($cartItems as $item) {
            $price = $item->price ?? 0;
            $qty = $item->qty ?? 1;
            $subtotal += ($price * $qty * $this->currencyRate);
        }
        
        $vat = ($subtotal * $vatPercent) / 100;
        $total = $subtotal + $shippingCharge + $vat;
        
        return [
            'subtotal' => $subtotal,
            'shipping' => $shippingCharge,
            'vat' => $vat,
            'vatPercent' => $vatPercent,
            'total' => $total,
        ];
    }
    
    public function processPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'address_id' => 'required|exists:customeraddress,customeraddressid',
            'shipping_method' => 'required|in:standard,express',
        ]);
        
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        
        // Process payment logic here
        // This will be implemented based on payment gateway integration
        
        return redirect()->route('thankyou', ['locale' => app()->getLocale()]);
    }
}

