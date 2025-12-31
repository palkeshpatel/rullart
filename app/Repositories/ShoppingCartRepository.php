<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ShoppingCartRepository
{
    /**
     * Get or create cart ID
     */
    public function getOrCreateCartId($customerId, $sessionId, $locale, $shippingcountryid)
    {
        // Check if cart exists
        if ($customerId > 0) {
            $cart = DB::table('shoppingcartmaster')
                ->where('fkcustomerid', $customerId)
                ->orderBy('cartid', 'desc')
                ->first();
        } else {
            $cart = DB::table('shoppingcartmaster')
                ->where('sessionid', $sessionId)
                ->where('fkcustomerid', 0)
                ->first();
        }

        if ($cart) {
            return $cart->cartid;
        }

        // Create new cart
        return DB::table('shoppingcartmaster')->insertGetId([
            'lang' => $locale,
            'sessionid' => $sessionId,
            'fkcustomerid' => $customerId,
            'shippingcountryid' => $shippingcountryid,
            'shipping_method' => '',
            'itemtotal' => 0,
            'shipping_charge' => 0,
            'total' => 0,
            'paymentmethod' => '',
            'addressid' => 0,
            'billingaddressid' => 0,
            'shippingaddressid' => 0,
            'couponcode' => '',
            'couponvalue' => 0,
            'discount' => 0,
            'mobiledevice' => '',
            'browser' => '',
            'platform' => '',
        ]);
    }

    /**
     * Get cart data with items
     */
    public function getCartData($shoppingCartId, $locale)
    {
        // Calculate total
        $total = DB::table('shoppingcartitems as s')
            ->select(DB::raw('ifnull(sum(s.sellingprice*s.qty),0) as totalamt'))
            ->join('products as p', 'p.productid', '=', 's.fkproductid')
            ->leftJoin('productsfilter as pfsize', function ($join) {
                $join->on('p.productid', '=', 'pfsize.fkproductid')
                    ->where('pfsize.filtercode', '=', 'size')
                    ->whereRaw('s.size=pfsize.fkfiltervalueid');
            })
            ->where('p.ispublished', 1)
            ->where('s.fkcartid', $shoppingCartId)
            ->value('totalamt') ?? 0;

        // Get cart master
        $cart = DB::table('shoppingcartmaster as s')
            ->select([
                's.cartid',
                's.fkcustomerid',
                's.sessionid',
                's.orderdate',
                's.paymentmethod',
                's.addressid',
                's.lang',
                DB::raw($total . ' as itemtotal'),
                DB::raw("ifnull(s.couponcode,'') as couponcode"),
                DB::raw("ifnull(s.couponvalue,'') as couponvalue"),
                DB::raw("ifnull(s.discount,0) as discount"),
                's.asGift',
                DB::raw("ifnull(s.giftMessage,'') as giftMessage"),
                's.shippingaddressid',
                's.billingaddressid',
                's.shippingcountryid',
                DB::raw("ifnull(c.email,'') as email"),
                's.shipping_charge',
            ])
            ->leftJoin('customers as c', 's.fkcustomerid', '=', 'c.customerid')
            ->where('s.cartid', $shoppingCartId)
            ->first();

        if (!$cart) {
            return [
                'shoppingcart' => null,
                'shoppingcartitems' => collect([])
            ];
        }

        // Get cart items
        if ($locale == 'ar') {
            $columns = "p.shortdescrAR as title, ifnull(filtersize.filtervalueAR, ifnull(s.sizename, '')) as sizename";
        } else {
            $columns = "p.shortdescr as title, ifnull(filtersize.filtervalue, ifnull(s.sizename, '')) as sizename";
        }

        $items = DB::table('shoppingcartitems as s')
            ->select(DB::raw($columns . ', s.cartitemid, p.photo1 as image, p.productid, p.productcode, s.price, s.sellingprice, s.subtotal, c.categorycode, c.parentid, p.discount, p.gift_type, s.qty, s.giftqty, ifnull(filtersize.filtervalueid, s.size) as size, ifnull(pfsize.qty, 0) as maxqty, s.giftproductid, s.giftproductid2, s.giftproductid3, s.giftproductid4, s.giftproductprice, s.giftproduct2price, s.giftproduct3price, s.giftproduct4price, s.giftboxprice, s.giftmessageid, ifnull(s.giftmessage,\'\') as giftmessage, s.gifttitleAR, s.gifttitle, p.fkcategoryid, s.giftmessage_charge, s.internation_ship'))
            ->distinct()
            ->join('products as p', 'p.productid', '=', 's.fkproductid')
            ->leftJoin('productsfilter as pfsize', function ($join) {
                $join->on('p.productid', '=', 'pfsize.fkproductid')
                    ->where('pfsize.filtercode', '=', 'size')
                    ->whereRaw('s.size=pfsize.fkfiltervalueid');
            })
            ->leftJoin('filtervalues as filtersize', 'filtersize.filtervalueid', '=', 'pfsize.fkfiltervalueid')
            ->leftJoin('category as c', 'p.fkcategoryid', '=', 'c.categoryid')
            ->where('s.fkcartid', $shoppingCartId)
            ->orderBy('s.cartitemid', 'asc')
            ->get();

        return [
            'shoppingcart' => $cart,
            'shoppingcartitems' => $items
        ];
    }

    /**
     * Get cart item count
     */
    public function getCartItemCount($shoppingCartId)
    {
        return DB::table('shoppingcartitems')
            ->where('fkcartid', $shoppingCartId)
            ->sum('qty') ?? 0;
    }

    /**
     * Get existing cart item
     */
    public function getExistingCartItem($cartId, $productId, $size, $giftmessage)
    {
        $query = DB::table('shoppingcartitems')
            ->where('fkcartid', $cartId)
            ->where('fkproductid', $productId)
            ->where('size', (int)$size);

        // Normalize giftmessage for comparison
        $normalizedGiftMessage = $giftmessage ? trim($giftmessage) : '';
        if (empty($normalizedGiftMessage)) {
            $query->where(function ($q) {
                $q->whereNull('giftmessage')
                  ->orWhere('giftmessage', '');
            });
        } else {
            $query->where('giftmessage', $normalizedGiftMessage);
        }

        return $query->first();
    }

    /**
     * Insert cart item
     */
    public function insertCartItem(array $data)
    {
        return DB::table('shoppingcartitems')->insertGetId($data);
    }

    /**
     * Update cart item
     */
    public function updateCartItem($cartId, $cartItemId, array $data)
    {
        return DB::table('shoppingcartitems')
            ->where('fkcartid', $cartId)
            ->where('cartitemid', $cartItemId)
            ->update($data);
    }

    /**
     * Remove cart item
     */
    public function removeCartItem($cartId, $cartItemId)
    {
        return DB::table('shoppingcartitems')
            ->where('fkcartid', $cartId)
            ->where('cartitemid', $cartItemId)
            ->delete();
    }

    /**
     * Get messages for gift messages
     */
    public function getMessages()
    {
        return DB::table('messages')
            ->orderBy('displayorder')
            ->get();
    }

    /**
     * Update cart session ID
     */
    public function updateCartSessionId($cartId, $sessionId)
    {
        return DB::table('shoppingcartmaster')
            ->where('cartid', $cartId)
            ->update(['sessionid' => $sessionId]);
    }
}

