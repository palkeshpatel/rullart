<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class ShoppingCartController extends FrontendController
{
    public function index()
    {
        $locale = app()->getLocale();
        $shoppingCartId = Session::get('shoppingcartid');

        if (!$shoppingCartId) {
            // Create new cart if doesn't exist
            $customerId = Session::get('customerid', 0);
            $sessionId = Session::getId();
            $shoppingCartId = $this->getOrCreateCartId($customerId, $sessionId);
            Session::put('shoppingcartid', $shoppingCartId);
        }

        // Get cart data
        $cartData = $this->getCartData($shoppingCartId);

        if (empty($cartData['shoppingcart'])) {
            Session::forget('shoppingcartid');
            return redirect()->route('home', ['locale' => $locale]);
        }

        return view('frontend.shoppingcart.index', $cartData);
    }

    protected function getOrCreateCartId($customerId, $sessionId)
    {
        $locale = app()->getLocale();
        $shippingcountryid = config('app.default_countryid', 1);

        // Check if cart exists (CI doesn't use isactive column)
        $cart = DB::table('shoppingcartmaster')
            ->where('fkcustomerid', $customerId)
            ->where('sessionid', $sessionId)
            ->first();

        if ($cart) {
            return $cart->cartid; // CI uses 'cartid' not 'shoppingcartid'
        }

        // Create new cart - match CI exactly: lang, sessionid, fkcustomerid, shippingcountryid, shipping_method
        // Also add required fields: itemtotal, shipping_charge (database requires them)
        $cartId = DB::table('shoppingcartmaster')->insertGetId([
            'lang' => $locale,
            'sessionid' => $sessionId,
            'fkcustomerid' => $customerId,
            'shippingcountryid' => $shippingcountryid,
            'shipping_method' => '',
            'itemtotal' => 0, // Required field, set to 0 initially
            'shipping_charge' => 0, // Required field, set to 0 initially
        ]);

        return $cartId;
    }

    protected function getCartData($shoppingCartId)
    {
        $cart = DB::table('shoppingcartmaster')
            ->where('cartid', $shoppingCartId)
            ->first();

        if (!$cart) {
            return [
                'shoppingcart' => null,
                'shoppingcartitems' => collect([])
            ];
        }

        $items = DB::table('shoppingcartitems as sci')
            ->select([
                'sci.*',
                'p.productcode',
                'p.shortdescr',
                'p.shortdescrAR',
                'p.title',
                'p.titleAR',
                'p.photo1',
                'c.categorycode',
                DB::raw("(SELECT sum(qty) FROM productsfilter WHERE fkproductid = p.productid AND filtercode = 'size') as available_qty")
            ])
            ->leftJoin('products as p', 'sci.fkproductid', '=', 'p.productid')
            ->leftJoin('category as c', 'p.fkcategoryid', '=', 'c.categoryid')
            ->where('sci.fkcartid', $shoppingCartId)
            ->get();

        return [
            'shoppingcart' => $cart,
            'shoppingcartitems' => $items
        ];
    }

    /**
     * AJAX cart endpoint - matches CI shoppingcart/ajax_cart
     */
    public function ajaxCart(Request $request)
    {
        $action = $request->input('action');

        if ($action == 'add') {
            return $this->addToCartAjax($request);
        } else if ($action == 'delete') {
            return $this->deleteFromCart($request);
        } else if ($action == 'giftmessage') {
            return $this->updateGiftMessage($request);
        }

        return response()->json(['status' => false, 'msg' => 'Invalid action']);
    }

    /**
     * Add to cart - matches CI ajax_cart action=add
     */
    protected function addToCartAjax(Request $request)
    {
        $locale = app()->getLocale();
        $pid = $request->input('p_id');
        $qty = $request->input('quantity', 1);
        $size = $request->input('size', '');
        $giftproductid = $request->input('giftproductid', 0);
        $giftproductid2 = $request->input('giftproductid2', 0);
        $giftproductid3 = $request->input('giftproductid3', 0);
        $giftproductid4 = $request->input('giftproductid4', 0);
        $gift_type = $request->input('gift_type', 0);
        $giftqty = $request->input('giftqty', 0);
        $giftmessageid = $request->input('giftmessageid', 0);
        $giftmessage = $request->input('giftmessage', '');

        if ($giftmessageid == "") {
            $giftmessageid = 0;
        }

        $giftmessage_charge = 0;
        $gifttitle = "";
        $gifttitleAR = "";

        if ($giftmessageid > 0) {
            $giftmessages = DB::table('messages')
                ->where('messageid', $giftmessageid)
                ->first();

            if ($giftmessages) {
                $gifttitle = $giftmessages->message ?? '';
                $gifttitleAR = $giftmessages->messageAR ?? '';
                $giftmessage_charge = DB::table('settings')
                    ->where('settingkey', 'Gift Message Charge')
                    ->value('settingvalue') ?? 0;
            }
        }

        if ($giftproductid == "") {
            $giftproductid = 0;
        }
        if ($giftproductid2 == "") {
            $giftproductid2 = 0;
        }
        if ($giftproductid3 == "") {
            $giftproductid3 = 0;
        }
        if ($giftproductid4 == "") {
            $giftproductid4 = 0;
        }
        if ($size == "") {
            $size = 0;
        }

        // Get product by ID and size
        $product = $this->productBySize($pid, $size);
        if (!$product) {
            return response(0);
        }

        $sellingprice = $product['sellingprice'];
        $available_qty = $product['qty'];

        // Check existing cart quantity
        $shoppingcartid = Session::get('shoppingcartid');
        if (!empty($shoppingcartid)) {
            $cartProductQty = $this->getCartProductQty($shoppingcartid, $pid, $size);
            if ($cartProductQty > 0) {
                $available_qty = $available_qty - $cartProductQty;
            }
        }

        if ($available_qty < $qty) {
            $qty = $available_qty;
        }

        if ($qty > 0) {
            $giftboxprice = 0;
            $giftproductprice = 0;
            $giftproduct2price = 0;
            $giftproduct3price = 0;
            $giftproduct4price = 0;

            if ($giftproductid > 0) {
                $giftboxprice = $product['sellingprice'];
            }

            if ($giftproductid > 0 && $gift_type == 1) {
                $giftproduct = $this->productBySize($giftproductid, 0);
                if ($giftproduct) {
                    $sellingprice = $sellingprice + $giftproduct['sellingprice'];
                    $giftproductprice = $giftproduct['sellingprice'];
                }
            }

            if ($giftproductid2 > 0 && $gift_type == 1) {
                $giftproduct2 = $this->productBySize($giftproductid2, 0);
                if ($giftproduct2) {
                    $sellingprice = $sellingprice + $giftproduct2['sellingprice'];
                    $giftproduct2price = $giftproduct2['sellingprice'];
                }
            }

            if ($giftproductid3 > 0 && $gift_type == 1) {
                $giftproduct3 = $this->productBySize($giftproductid3, 0);
                if ($giftproduct3) {
                    $sellingprice = $sellingprice + $giftproduct3['sellingprice'];
                    $giftproduct3price = $giftproduct3['sellingprice'];
                }
            }

            if ($giftproductid4 > 0 && $gift_type == 1) {
                $giftproduct4 = $this->productBySize($giftproductid4, 0);
                if ($giftproduct4) {
                    $sellingprice = $sellingprice + $giftproduct4['sellingprice'];
                    $giftproduct4price = $giftproduct4['sellingprice'];
                }
            }

            $price = $sellingprice;

            $customerid = Session::get('customerid', 0);
            $shoppingcartid = Session::get('shoppingcartid');
            $sessionid = Session::getId();

            if ($shoppingcartid == "" || $shoppingcartid == "0") {
                $shippingcountryid = config('app.default_countryid', 1);
                $cartMaster = $this->cartMaster($customerid, $sessionid, $locale, $shippingcountryid);
                $shoppingcartid = 0;
                if ($cartMaster !== false) {
                    $shoppingcartid = $cartMaster['cartid'];
                }
                Session::put('shoppingcartid', $shoppingcartid);
            }

            // Prepare cart item data
            $pdata = [
                'id' => $product['productid'],
                'cartid' => $shoppingcartid,
                'qty' => $qty,
                'price' => $sellingprice,
                'sellingprice' => $price,
                'name' => str_replace('/', ':', $product['title']),
                'discount' => $product['discount'],
                'image' => $product['photo1'] == "" ? "noimage.jpg" : $product['photo1'],
                'options' => [
                    'size' => $size,
                    'sizeName' => $product['filtervalue'] ?? '',
                    'productcode' => $product['productcode'],
                    'categorycode' => $product['categorycode'],
                    'descr' => $product['title'],
                    'internation_ship' => $product['internation_ship'] ?? 1,
                    'giftproductid' => $giftproductid,
                    'giftproductid2' => $giftproductid2,
                    'giftproductid3' => $giftproductid3,
                    'giftproductid4' => $giftproductid4,
                    'giftproductprice' => $giftproductprice,
                    'giftproduct2price' => $giftproduct2price,
                    'giftproduct3price' => $giftproduct3price,
                    'giftproduct4price' => $giftproduct4price,
                    'giftboxprice' => $giftboxprice,
                    'giftmessageid' => $giftmessageid,
                    'giftmessage' => $giftmessage,
                    'gifttitle' => $gifttitle,
                    'gifttitleAR' => $gifttitleAR,
                    'giftwrap' => 0,
                    'giftmessage_charge' => $giftmessage_charge,
                    'available_qty' => $available_qty,
                    'giftqty' => $giftqty
                ]
            ];

            $returnarr = $this->cartInsert($pdata);

            // Remove from wishlist
            if ($customerid > 0) {
                DB::table('wishlist')
                    ->where('fkcustomerid', $customerid)
                    ->where('fkproductid', $pid)
                    ->delete();

                $wishlistCount = DB::table('wishlist')
                    ->where('fkcustomerid', $customerid)
                    ->count();
                Session::put('wishlist_item_cnt', $wishlistCount);
            }

            // Check cart quantities
            $this->cartQtyCheck($shoppingcartid);

            $cart_cnt = $returnarr["cnt"];
            return response($cart_cnt);
        } else {
            $shoppingcartid = Session::get('shoppingcartid');
            if (!empty($shoppingcartid)) {
                $cnt = $this->getCartItemCount($shoppingcartid);
                return response($cnt);
            } else {
                return response(0);
            }
        }
    }

    /**
     * Delete from cart - matches CI ajax_cart action=delete
     */
    protected function deleteFromCart(Request $request)
    {
        $cartitemid = $request->input('rowid');
        $shoppingcartid = Session::get('shoppingcartid');

        $returnarr = $this->cartRemove($shoppingcartid, $cartitemid);

        // Load cart view (if needed)
        $cartview = view('frontend.shoppingcart.cartview', [
            'shoppingcartid' => $shoppingcartid
        ])->render();

        return response()->json([
            'status' => true,
            'cnt' => $returnarr['cnt'],
            'cartview' => $cartview
        ]);
    }

    /**
     * Update gift message - matches CI ajax_cart action=giftmessage
     */
    protected function updateGiftMessage(Request $request)
    {
        $cartitemid = $request->input('rowid');
        $gift_type = $request->input('gift_type');
        $shoppingcartid = Session::get('shoppingcartid');

        $data = $request->input('data');
        $giftmessageid = $data["giftmessageid"] ?? 0;
        $giftmessage = $data["giftmessage"] ?? '';
        $gifttitle = $data["gifttitle"] ?? '';

        $gifttitle = "";
        $gifttitleAR = "";
        if ($giftmessageid == "") {
            $giftmessageid = 0;
        }
        $giftmessage_charge = 0;

        $cartitems = $this->getCartItem($cartitemid);
        if (!$cartitems) {
            return response()->json(['status' => false, 'msg' => 'Cart item not found']);
        }

        $pid = $cartitems['productid'];
        $size = $cartitems['size'];
        $qty = $cartitems['qty'];
        $giftproductid = $cartitems['giftproductid'] ?? 0;
        $giftproductid2 = $cartitems['giftproductid2'] ?? 0;
        $giftproductid3 = $cartitems['giftproductid3'] ?? 0;
        $giftproductid4 = $cartitems['giftproductid4'] ?? 0;

        $product = $this->productBySize($pid, $size);
        if (!$product) {
            return response()->json(['status' => false, 'msg' => 'Product not found']);
        }

        $sellingprice = $product['sellingprice'];

        if ($giftproductid > 0) {
            $giftboxprice = $product['sellingprice'];
        }

        if ($giftproductid > 0 && $gift_type == 1) {
            $giftproduct = $this->productBySize($giftproductid, 0);
            if ($giftproduct) {
                $sellingprice = $sellingprice + $giftproduct['sellingprice'];
                $giftproductprice = $giftproduct['sellingprice'];
            }
        }

        if ($giftproductid2 > 0 && $gift_type == 1) {
            $giftproduct2 = $this->productBySize($giftproductid2, 0);
            if ($giftproduct2) {
                $sellingprice = $sellingprice + $giftproduct2['sellingprice'];
                $giftproduct2price = $giftproduct2['sellingprice'];
            }
        }

        if ($giftproductid3 > 0 && $gift_type == 1) {
            $giftproduct3 = $this->productBySize($giftproductid3, 0);
            if ($giftproduct3) {
                $sellingprice = $sellingprice + $giftproduct3['sellingprice'];
                $giftproduct3price = $giftproduct3['sellingprice'];
            }
        }

        if ($giftproductid4 > 0 && $gift_type == 1) {
            $giftproduct4 = $this->productBySize($giftproductid4, 0);
            if ($giftproduct4) {
                $sellingprice = $sellingprice + $giftproduct4['sellingprice'];
                $giftproduct4price = $giftproduct4['sellingprice'];
            }
        }

        $giftqty = 0;
        if ($giftmessageid > 0) {
            $giftmessages = DB::table('messages')
                ->where('messageid', $giftmessageid)
                ->first();

            if ($giftmessages) {
                $gifttitle = $giftmessages->message ?? '';
                $gifttitleAR = $giftmessages->messageAR ?? '';
                $giftmessage_charge = DB::table('settings')
                    ->where('settingkey', 'Gift Message Charge')
                    ->value('settingvalue') ?? 0;
            }
            $giftqty = $cartitems['giftqty'] ?? 0;
        }

        $updateData = [
            'giftmessageid' => $giftmessageid,
            'gifttitle' => $gifttitle,
            'gifttitleAR' => $gifttitleAR,
            'giftmessage' => $giftmessage,
            'price' => $sellingprice,
            'sellingprice' => $sellingprice,
            'giftqty' => $giftqty,
            'giftmessage_charge' => $giftmessage_charge,
            'subtotal' => $qty * $sellingprice,
        ];

        $this->cartUpdate($shoppingcartid, $cartitemid, $updateData);

        $msg = __('Gift message updated');

        $cartview = view('frontend.shoppingcart.cartview', [
            'shoppingcartid' => $shoppingcartid
        ])->render();

        $giftmessagecart = "";
        if ($gifttitle != "") {
            $giftmessagecart = '<p class="occassion">' . $gifttitle . '</p>';
        }
        if ($giftmessage != "") {
            $giftmessagecart = $giftmessagecart . '<p class="occassion">' . nl2br($giftmessage) . '</p>';
        }
        if ($giftmessagecart != "") {
            $giftmessagecart = $giftmessagecart . '<a href="#" class="btn-edit">Edit</a>';
        }

        return response()->json([
            'status' => true,
            'msg' => $msg,
            'cartview' => $cartview,
            'giftmessagecart' => $giftmessagecart
        ]);
    }

    /**
     * Get product by ID and size - matches CI product_by_Id_Size
     */
    protected function productBySize($id, $sizeid)
    {
        $locale = app()->getLocale();

        if ($locale == 'ar') {
            $columns = 'p.shortdescrAR as title, p.productid, p.productcode, p.price, pp.discount, pp.sellingprice, p.photo1, p.titleAR as shortdescr, p.internation_ship, pf.qty, fv.filtervalue, c.categorycode';
        } else {
            $columns = 'p.shortdescr as title, p.productid, p.productcode, p.price, pp.discount, pp.sellingprice, p.photo1, p.title as shortdescr, p.internation_ship, pf.qty, fv.filtervalue, c.categorycode';
        }

        $product = DB::table('products as p')
            ->select(DB::raw($columns))
            ->join('productpriceview as pp', 'pp.kproductid', '=', 'p.productid')
            ->join('category as c', 'p.fkcategoryid', '=', 'c.categoryid')
            ->leftJoin('productsfilter as pf', function ($join) use ($sizeid) {
                $join->on('pf.fkproductid', '=', 'p.productid')
                    ->where('pf.filtercode', '=', 'size')
                    ->where('pf.fkfiltervalueid', '=', $sizeid);
            })
            ->leftJoin('filtervalues as fv', 'fv.filtervalueid', '=', 'pf.fkfiltervalueid')
            ->where('p.productid', $id)
            ->first();

        if ($product) {
            return (array) $product;
        }

        return false;
    }

    /**
     * Get or create cart master - matches CI cart_master
     */
    protected function cartMaster($customerid, $sessionid, $lang, $shippingcountryid)
    {
        if ($customerid == null) {
            $customerid = 0;
        }

        $cartid = false;

        if ($customerid == 0 && $sessionid == "") {
            $uniqueId = time() . "-" . rand(1, 100);
            if ($shippingcountryid == "") {
                $shippingcountryid = config('app.default_countryid', 1);
            }

            $cartid = DB::table('shoppingcartmaster')->insertGetId([
                'lang' => $lang,
                'sessionid' => $uniqueId,
                'fkcustomerid' => $customerid,
                'shippingcountryid' => $shippingcountryid,
                'shipping_method' => '',
                'itemtotal' => 0, // Required field, set to 0 initially
                'shipping_charge' => 0, // Required field, set to 0 initially
            ]);
        } else if ($customerid == 0 && $sessionid != "") {
            $cart = DB::table('shoppingcartmaster')
                ->where('sessionid', $sessionid)
                ->first();

            if ($cart) {
                $cartid = $cart->cartid;
            }
        } else if ($customerid > 0) {
            $cart = DB::table('shoppingcartmaster')
                ->where('fkcustomerid', $customerid)
                ->orderBy('cartid', 'desc')
                ->first();

            if ($cart) {
                $cartid = $cart->cartid;
                if ($cart->shippingcountryid != $shippingcountryid && $shippingcountryid != '') {
                    DB::table('shoppingcartmaster')
                        ->where('cartid', $cartid)
                        ->update(['shippingcountryid' => $shippingcountryid]);
                }
            } else {
                $sessionid = "";
                if ($shippingcountryid == "") {
                    $shippingcountryid = config('app.default_countryid', 1);
                }

                $cartid = DB::table('shoppingcartmaster')->insertGetId([
                    'lang' => $lang,
                    'sessionid' => $sessionid,
                    'fkcustomerid' => $customerid,
                    'shippingcountryid' => $shippingcountryid,
                    'shipping_method' => '',
                    'itemtotal' => 0, // Required field, set to 0 initially
                    'shipping_charge' => 0, // Required field, set to 0 initially
                ]);
            }
        }

        if ($cartid == false) {
            return false;
        }

        // Calculate total
        $total = DB::table('shoppingcartitems as s')
            ->select(DB::raw('ifnull(sum(s.sellingprice*s.qty),0) as totalamt'))
            ->join('products as p', 'p.productid', '=', 's.fkproductid')
            ->join('productsfilter as pfsize', function ($join) {
                $join->on('p.productid', '=', 'pfsize.fkproductid')
                    ->where('pfsize.filtercode', '=', 'size')
                    ->whereRaw('s.size=pfsize.fkfiltervalueid');
            })
            ->where('p.ispublished', 1)
            ->where('s.fkcartid', $cartid)
            ->value('totalamt') ?? 0;

        $cartMaster = DB::table('shoppingcartmaster as s')
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
                's.shipping_charge',
                's.shipping_method',
                's.asGift',
                DB::raw("ifnull(s.giftMessage,'') as giftMessage"),
                's.shippingaddressid',
                's.billingaddressid',
                's.shippingcountryid',
            ])
            ->where('s.cartid', $cartid)
            ->first();

        if ($cartMaster) {
            return (array) $cartMaster;
        }

        return false;
    }

    /**
     * Insert cart item - matches CI cart_insert
     */
    protected function cartInsert($pdata)
    {
        $fkproductid = $pdata["id"];
        $cartid = $pdata["cartid"];
        $price = $pdata["price"];
        $qty = $pdata["qty"];
        $giftqty = $pdata["options"]['giftqty'] ?? 0;
        $title = $pdata["name"];
        $sellingprice = $pdata["sellingprice"];
        $size = $pdata["options"]["size"];
        $sizename = $size == 0 ? "" : ($pdata["options"]["sizeName"] ?? "");
        $discount = $pdata["discount"];
        $subtotal = $pdata["price"] * $pdata["qty"];
        $photo = $pdata["image"];
        $giftproductid = $pdata["options"]["giftproductid"] ?? 0;
        $giftproductid2 = $pdata["options"]["giftproductid2"] ?? 0;
        $giftproductid3 = $pdata["options"]["giftproductid3"] ?? 0;
        $giftproductid4 = $pdata["options"]["giftproductid4"] ?? 0;
        $giftproductprice = $pdata["options"]["giftproductprice"] ?? 0;
        $giftproduct2price = $pdata["options"]["giftproduct2price"] ?? 0;
        $giftproduct3price = $pdata["options"]["giftproduct3price"] ?? 0;
        $giftproduct4price = $pdata["options"]["giftproduct4price"] ?? 0;
        $giftboxprice = $pdata["options"]["giftboxprice"] ?? 0;
        $internation_ship = $pdata["options"]["internation_ship"] ?? 1;
        $giftmessageid = $pdata["options"]["giftmessageid"] ?? 0;
        $gifttitle = $pdata["options"]["gifttitle"] ?? '';
        $gifttitleAR = $pdata["options"]["gifttitleAR"] ?? '';
        $giftmessage = $pdata["options"]["giftmessage"] ?? '';
        $giftmessage_charge = $pdata["options"]["giftmessage_charge"] ?? 0;

        // Check if item exists
        $existingItem = DB::table('shoppingcartitems')
            ->where('fkproductid', $fkproductid)
            ->where('size', $size)
            ->where('fkcartid', $cartid)
            ->where('giftmessageid', $giftmessageid)
            ->where('giftmessage', $giftmessage)
            ->first();

        $cartitemid = 0;
        if ($existingItem) {
            $cartitemid = $existingItem->cartitemid;
        }

        $status = true;

        if ($cartitemid == 0) {
            // Insert new item
            DB::table('shoppingcartitems')->insert([
                'fkproductid' => $fkproductid,
                'qty' => $qty,
                'giftqty' => $giftqty,
                'price' => $price,
                'title' => $title,
                'size' => $size,
                'sellingprice' => $sellingprice,
                'sizename' => $sizename,
                'discount' => $discount,
                'subtotal' => $subtotal,
                'photo' => $photo, // Note: CI stores as 'photo' in cart items table
                'internation_ship' => $internation_ship,
                'giftproductid' => $giftproductid,
                'giftproductid2' => $giftproductid2,
                'giftproductid3' => $giftproductid3,
                'giftproductid4' => $giftproductid4,
                'giftproductprice' => $giftproductprice,
                'giftproduct2price' => $giftproduct2price,
                'giftproduct3price' => $giftproduct3price,
                'giftproduct4price' => $giftproduct4price,
                'giftboxprice' => $giftboxprice,
                'giftmessageid' => $giftmessageid,
                'giftmessage' => $giftmessage,
                'gifttitle' => $gifttitle,
                'gifttitleAR' => $gifttitleAR,
                'giftmessage_charge' => $giftmessage_charge,
                'fkcartid' => $cartid,
                'createdon' => now(),
            ]);

            $msg = __('Product added in cart');
        } else {
            // Update existing item
            $pid = $existingItem->fkproductid;
            $size = $existingItem->size;
            $cartqty = $existingItem->qty;
            $qty = $qty + $cartqty;

            $product = $this->productBySize($pid, $size);

            if ($qty > $product['qty']) {
                $msg = __("Only") . " " . $product['qty'] . " " . __("available.");
                $status = false;
            } else {
                $subtotal = $price * $qty;

                DB::table('shoppingcartitems')
                    ->where('fkcartid', $cartid)
                    ->where('cartitemid', $cartitemid)
                    ->update([
                        'qty' => $qty,
                        'subtotal' => $subtotal
                    ]);

                $msg = __("Product quantity updated!");
                $status = true;
            }
        }

        $cnt = DB::table('shoppingcartitems')
            ->where('fkcartid', $cartid)
            ->count();

        return [
            'status' => $status,
            'msg' => $msg,
            'cnt' => $cnt,
            'fkcartid' => $cartid
        ];
    }

    /**
     * Remove cart item - matches CI cart_remove
     */
    protected function cartRemove($cartid, $cartitemid)
    {
        DB::table('shoppingcartitems')
            ->where('fkcartid', $cartid)
            ->where('cartitemid', $cartitemid)
            ->delete();

        $msg = __('Product has been deleted from your cart!');

        $cnt = DB::table('shoppingcartitems')
            ->where('fkcartid', $cartid)
            ->count();

        return [
            'msg' => $msg,
            'cnt' => $cnt
        ];
    }

    /**
     * Update cart item - matches CI cart_update
     */
    protected function cartUpdate($cartid, $cartitemid, $data)
    {
        DB::table('shoppingcartitems')
            ->where('fkcartid', $cartid)
            ->where('cartitemid', $cartitemid)
            ->update($data);

        return true;
    }

    /**
     * Get cart item - matches CI getCartItem
     */
    protected function getCartItem($cartitemid)
    {
        $locale = app()->getLocale();

        if ($locale == 'ar') {
            $columns = "p.shortdescrAR as title, filtersize.filtervalueAR as sizename, gifttitleAR as gifttitle";
        } else {
            $columns = "p.shortdescr as title, filtersize.filtervalue as sizename, gifttitle";
        }

        $item = DB::table('shoppingcartitems as s')
            ->select(DB::raw($columns . ', s.cartitemid, s.photo as image, s.fkproductid as productid, s.productcode, s.price, s.fkcartid, s.sellingprice, p.discount, s.qty, filtersize.filtervalueid as size, pfsize.qty as maxqty, s.giftproductid, s.giftproductid2, s.giftproductid3, s.giftproductid4, s.giftproductprice, s.giftproduct2price, s.giftproduct3price, s.giftproduct4price, s.giftboxprice, s.giftmessageid, s.giftmessage, s.subtotal'))
            ->join('products as p', 'p.productid', '=', 's.fkproductid')
            ->join('productpriceview as pp', 'pp.kproductid', '=', 'p.productid')
            ->join('productsfilter as pfsize', function ($join) {
                $join->on('p.productid', '=', 'pfsize.fkproductid')
                    ->where('pfsize.filtercode', '=', 'size')
                    ->whereRaw('s.size=pfsize.fkfiltervalueid');
            })
            ->join('filtervalues as filtersize', 'pfsize.fkfiltervalueid', '=', 'filtersize.filtervalueid')
            ->where('p.ispublished', 1)
            ->where('s.cartitemid', $cartitemid)
            ->first();

        if ($item) {
            return (array) $item;
        }

        return false;
    }

    /**
     * Check cart quantities - matches CI cart_qty_check
     */
    protected function cartQtyCheck($cartid)
    {
        $cartitems = $this->getCartItems($cartid);

        foreach ($cartitems as $data) {
            $cartitemid = $data->cartitemid;
            if ($data->size == null) {
                $this->cartRemove($cartid, $cartitemid);
            } else {
                $avlqty = $data->maxqty ?? 0;
                if ($avlqty <= 0) {
                    $this->cartRemove($cartid, $cartitemid);
                } elseif ($avlqty < $data->qty) {
                    $this->cartUpdate($cartid, $cartitemid, ['qty' => $avlqty]);
                }
            }
        }

        return true;
    }

    /**
     * Get cart items - helper for cart_qty_check
     */
    protected function getCartItems($cartid)
    {
        $locale = app()->getLocale();

        if ($locale == 'ar') {
            $columns = "p.shortdescrAR as title, filtersize.filtervalueAR as sizename";
        } else {
            $columns = "p.shortdescr as title, filtersize.filtervalue as sizename";
        }

        return DB::table('shoppingcartitems as s')
            ->select(DB::raw($columns . ', s.cartitemid, s.productid, s.qty, filtersize.filtervalueid as size, pfsize.qty as maxqty'))
            ->join('products as p', 'p.productid', '=', 's.fkproductid')
            ->join('productsfilter as pfsize', function ($join) {
                $join->on('p.productid', '=', 'pfsize.fkproductid')
                    ->where('pfsize.filtercode', '=', 'size')
                    ->whereRaw('s.size=pfsize.fkfiltervalueid');
            })
            ->join('filtervalues as filtersize', 'pfsize.fkfiltervalueid', '=', 'filtersize.filtervalueid')
            ->where('p.ispublished', 1)
            ->where('s.fkcartid', $cartid)
            ->get();
    }

    /**
     * Get cart product quantity - helper method
     */
    protected function getCartProductQty($cartid, $productId, $sizeId)
    {
        $query = DB::table('shoppingcartitems as s')
            ->select(DB::raw('SUM(s.qty) as total_qty'))
            ->where('s.fkcartid', $cartid)
            ->where('s.fkproductid', $productId);

        if (!empty($sizeId)) {
            $query->where('s.size', $sizeId);
        }

        $result = $query->first();
        return $result ? ($result->total_qty ?? 0) : 0;
    }

    /**
     * Get cart item count - helper method
     */
    protected function getCartItemCount($cartid)
    {
        return DB::table('shoppingcartitems')
            ->where('fkcartid', $cartid)
            ->count();
    }

    /**
     * AJAX endpoint for wishlist operations (matches CI shoppingcart/ajax_wishlist)
     */
    public function ajaxWishlist(Request $request)
    {
        $action = $request->input('action');
        $productId = $request->input('pid');
        $customerId = Session::get('customerid');

        if (!$customerId) {
            return response('login');
        }

        if ($action == 'delete' && $productId) {
            // Delete wishlist item by product ID
            DB::table('wishlist')
                ->where('fkcustomerid', $customerId)
                ->where('fkproductid', $productId)
                ->delete();

            // Get updated count
            $count = DB::table('wishlist')
                ->where('fkcustomerid', $customerId)
                ->count();

            Session::put('wishlist_item_cnt', $count);

            // Return count as string (CI returns it as string/number)
            return response($count);
        }

        return response()->json(['status' => false, 'msg' => 'Invalid action']);
    }
}