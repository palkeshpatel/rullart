<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class CartCalculationService
{
    /**
     * Calculate cart totals including discounts, VAT, and shipping
     */
    public function calculateCartTotals(Collection $cartItems, $shippingCharge = 0, $vatPercent = 0)
    {
        $total = 0;
        $giftMessageCharge = $this->getGiftMessageCharge();
        
        foreach ($cartItems as $item) {
            $subtotal = $item->subtotal;
            
            // Add gift message charge if applicable
            $giftmessageid = $item->giftmessageid ?? 0;
            $giftqty = $item->giftqty ?? 0;
            
            if (!empty($giftqty) && !empty($giftmessageid)) {
                $subtotal += $giftMessageCharge * $giftqty;
            }
            
            // Check international shipping
            $internation_ship = $item->internation_ship ?? 1;
            $shippingCountry = Session::get('shipping_country', config('app.default_country', 'Kuwait'));
            
            if ($internation_ship == 0 && $shippingCountry != config('app.default_country', 'Kuwait')) {
                // Item not available for international shipping
                continue;
            }
            
            $total += $subtotal;
        }
        
        // Get coupon data from session
        $couponvalue = Session::get('couponvalue', 0);
        $couponcode = Session::get('couponcode', '');
        $coupontype = Session::get('coupontype', '');
        $fkcoupontypeid = Session::get('fkcoupontypeid', 0);
        $couponcategoryid = Session::get('couponcategoryid', []);
        
        // Calculate discount
        $discountvalue = $this->calculateDiscount(
            $total,
            $cartItems,
            $couponvalue,
            $coupontype,
            $fkcoupontypeid,
            $couponcategoryid,
            $shippingCountry
        );
        
        // Calculate VAT
        $vat = 0;
        if ($vatPercent > 0) {
            $vat = (($total + $shippingCharge - $discountvalue) * $vatPercent) / 100;
        }
        
        $carttotal = $total - $discountvalue + $vat + $shippingCharge;
        Session::put('carttotal', $carttotal);
        
        return [
            'total' => $total,
            'discountvalue' => $discountvalue,
            'vat' => $vat,
            'shippingCharge' => $shippingCharge,
            'carttotal' => $carttotal
        ];
    }
    
    /**
     * Calculate discount based on coupon
     */
    protected function calculateDiscount($total, Collection $cartItems, $couponvalue, $coupontype, $fkcoupontypeid, $couponcategoryid, $shippingCountry)
    {
        if ($couponvalue <= 0) {
            return 0;
        }
        
        if ($coupontype == "category") {
            $coupontotal = 0;
            foreach ($cartItems as $item) {
                $collectionarr = DB::table('category')
                    ->where('categoryid', $item->fkcategoryid)
                    ->get();
                $applied = 0;
                foreach ($collectionarr as $collection) {
                    $internation_ship = $item->internation_ship ?? 1;
                    if ($internation_ship == 0 && $shippingCountry != config('app.default_country', 'Kuwait')) {
                        continue;
                    } else if (in_array($collection->parentid, $couponcategoryid) && $applied == 0) {
                        $subtotal = $item->subtotal;
                        $coupontotal += $subtotal;
                        $applied = 1;
                    }
                }
            }
            
            if ($fkcoupontypeid == 2) {
                $discountvalue = min($coupontotal, $couponvalue);
            } else {
                $discountvalue = ($coupontotal * $couponvalue) / 100;
            }
        } else {
            $coupontotal = $total;
            if ($fkcoupontypeid == 2) {
                $discountvalue = min($coupontotal, $couponvalue);
            } else {
                $discountvalue = ($coupontotal * $couponvalue) / 100;
            }
        }
        
        if ($total < $discountvalue) {
            $discountvalue = $total;
        }
        
        return $discountvalue;
    }
    
    /**
     * Get gift message charge from settings
     */
    protected function getGiftMessageCharge()
    {
        return DB::table('settings')
            ->where('name', 'Gift Message Charge')
            ->value('details') ?? 0;
    }
    
    /**
     * Calculate item subtotal with gift message charge
     */
    public function calculateItemSubtotal($itemSubtotal, $giftmessageid, $giftqty)
    {
        $giftMessageCharge = $this->getGiftMessageCharge();
        $subtotal = $itemSubtotal;
        
        if (!empty($giftqty) && !empty($giftmessageid)) {
            $subtotal += $giftMessageCharge * $giftqty;
        }
        
        return $subtotal;
    }
    
    /**
     * Check if item should be struck through (not available for shipping)
     */
    public function shouldStrikeItem($internation_ship, $shippingCountry)
    {
        return $internation_ship == 0 && $shippingCountry != config('app.default_country', 'Kuwait');
    }
}

