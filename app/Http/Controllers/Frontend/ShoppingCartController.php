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
        Log::info('=== SHOW CART START ===', [
            'session_id' => Session::getId(),
            'customerid' => Session::get('customerid', 0),
            'shoppingcartid' => Session::get('shoppingcartid')
        ]);

        $locale = app()->getLocale();
        $shoppingCartId = Session::get('shoppingcartid');

        // If no cart ID in session, try to retrieve by customerid/sessionid (matches CI behavior)
        if (!$shoppingCartId) {
            $customerId = Session::get('customerid', 0);
            $sessionId = Session::getId();
            $shoppingCartId = $this->getOrCreateCartId($customerId, $sessionId);
            if ($shoppingCartId) {
                Session::put('shoppingcartid', $shoppingCartId);
            }
        }

        // Get cart data
        Log::info('Getting cart data', ['cartid' => $shoppingCartId]);
        $cartData = $this->getCartData($shoppingCartId);

        Log::info('Cart data retrieved', [
            'has_cart' => !empty($cartData['shoppingcart']),
            'items_count' => $cartData['shoppingcartitems']->count() ?? 0,
            'cartid' => $cartData['shoppingcart']->cartid ?? 'NOT SET'
        ]);

        // Add messages for gift message dropdown (matches CI)
        $cartData['messages'] = DB::table('messages')
            ->orderBy('displayorder')
            ->get();

        // If cart is empty, show empty message
        if (empty($cartData['shoppingcart']) || ($cartData['shoppingcartitems'] && $cartData['shoppingcartitems']->count() == 0)) {
            Session::forget('shoppingcartid');
            // If AJAX request (overlay), return empty cart message
            if (request()->ajax() || request()->has('t')) {
                return view('frontend.shoppingcart.content', $cartData)->render();
            }
            // For full page, still show the cart page with empty message
        }

        // If AJAX request (overlay), return just the content without layout
        if (request()->ajax() || request()->has('t')) {
            return view('frontend.shoppingcart.content', $cartData)->render();
        }

        // Full page view (for direct navigation)
        Log::info('=== SHOW CART END ===', [
            'items_count' => $cartData['shoppingcartitems']->count() ?? 0
        ]);

        return view('frontend.shoppingcart.index', $cartData);
    }

    protected function getOrCreateCartId($customerId, $sessionId)
    {
        $locale = app()->getLocale();
        $shippingcountryid = config('app.default_countryid', 1);

        // Check if cart exists - match CI logic exactly
        // If customerid > 0, check by customerid (most recent)
        // If customerid == 0, check by sessionid
        if ($customerId > 0) {
            $cart = DB::table('shoppingcartmaster')
                ->where('fkcustomerid', $customerId)
                ->orderBy('cartid', 'desc')
                ->first();
        } else {
            // Guest user - check by sessionid
            $cart = DB::table('shoppingcartmaster')
                ->where('sessionid', $sessionId)
                ->where('fkcustomerid', 0)
                ->first();
        }

        if ($cart) {
            return $cart->cartid; // CI uses 'cartid' not 'shoppingcartid'
        }

        // Create new cart - match CI exactly: lang, sessionid, fkcustomerid, shippingcountryid, shipping_method
        // Also add required fields: itemtotal, shipping_charge, total (database requires them)
        $cartId = DB::table('shoppingcartmaster')->insertGetId([
            'lang' => $locale,
            'sessionid' => $sessionId,
            'fkcustomerid' => $customerId,
            'shippingcountryid' => $shippingcountryid,
            'shipping_method' => '',
            'itemtotal' => 0, // Required field, set to 0 initially
            'shipping_charge' => 0, // Required field, set to 0 initially
            'total' => 0, // Required field, set to 0 initially
            'paymentmethod' => '', // Required field
            'addressid' => 0, // Required field
            'billingaddressid' => 0, // Required field with default
            'shippingaddressid' => 0, // Required field with default
            'couponcode' => '', // Required field
            'couponvalue' => 0, // Required field
            'discount' => 0, // Required field
            'mobiledevice' => '', // Required field
            'browser' => '', // Required field
            'platform' => '', // Required field
        ]);

        return $cartId;
    }

    protected function getCartData($shoppingCartId)
    {
        // Get cart master - match CI cart_master_get
        // Use LEFT JOIN for size to handle products without sizes (size = 0)
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

        // Get cart items - match CI cart_get_items exactly
        // CI uses INNER JOIN which means items with size=0 won't match unless there's a productsfilter record
        // But we need to handle both cases: items with size > 0 AND items with size = 0
        $locale = app()->getLocale();

        // First, let's check what items actually exist in the database
        $rawItems = DB::table('shoppingcartitems as s')
            ->where('s.fkcartid', $shoppingCartId)
            ->get(['cartitemid', 'fkproductid', 'size', 'qty', 'sizename']);

        Log::info('getCartData: Raw items from DB', [
            'cartid' => $shoppingCartId,
            'raw_items' => $rawItems->toArray()
        ]);

        // Use a single query with LEFT JOIN for size filter to handle both cases
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
            ->leftJoin('filtervalues as filtersize', 'pfsize.fkfiltervalueid', '=', 'filtersize.filtervalueid')
            ->leftJoin('category as c', 'c.categoryid', '=', 'p.fkcategoryid')
            ->where('p.ispublished', 1)
            ->where('s.fkcartid', $shoppingCartId)
            ->where(function ($query) {
                // Include items with size > 0 that have qty > 0, OR items with size = 0
                $query->where(function ($q) {
                    $q->where('s.size', '>', 0)
                        ->where('pfsize.qty', '>', 0);
                })->orWhere('s.size', '=', 0);
            })
            ->get();

        Log::info('getCartData: Query executed', [
            'cartid' => $shoppingCartId,
            'items_count' => $items->count(),
            'items' => $items->map(function ($item) {
                return [
                    'cartitemid' => $item->cartitemid,
                    'productid' => $item->productid,
                    'size' => $item->size,
                    'qty' => $item->qty
                ];
            })->toArray()
        ]);

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
        Log::info('=== ADD TO CART START ===', [
            'request_data' => $request->all(),
            'session_id' => Session::getId(),
            'customerid' => Session::get('customerid', 0),
            'shoppingcartid' => Session::get('shoppingcartid')
        ]);

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
                    ->where('name', 'Gift Message Charge')
                    ->value('details') ?? 0;
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
        Log::info('Getting product by size', ['productid' => $pid, 'size' => $size]);
        $product = $this->productBySize($pid, $size);
        if (!$product) {
            Log::error('Product not found in productBySize', [
                'productid' => $pid,
                'size' => $size
            ]);
            return response(0);
        }

        Log::info('Product found', [
            'productid' => $product['productid'],
            'sellingprice' => $product['sellingprice'],
            'qty' => $product['qty'] ?? 0
        ]);

        $sellingprice = $product['sellingprice'];
        $available_qty = $product['qty'];

        // Don't reduce available_qty by cart quantity here
        // cartInsert will handle checking if item exists and updating quantity properly
        // It will add new qty to existing qty, then check against available stock

        Log::info('Checking available quantity', [
            'productid' => $pid,
            'size' => $size,
            'available_qty' => $available_qty,
            'requested_qty' => $qty
        ]);

        // Only limit qty if it exceeds total available stock (basic validation)
        // cartInsert will do the final check after combining with existing cart qty
        if ($qty > $available_qty) {
            $qty = $available_qty;
            Log::info('Limited qty to available stock', ['qty' => $qty]);
        }

        // Always call cartInsert if qty > 0
        // cartInsert will handle: checking if item exists, updating quantity, and stock validation
        if ($qty > 0) {
            Log::info('Proceeding to cartInsert', ['qty' => $qty, 'available_qty' => $available_qty]);
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

            Log::info('Checking cart ID', [
                'shoppingcartid' => $shoppingcartid,
                'customerid' => $customerid,
                'sessionid' => $sessionid
            ]);

            if ($shoppingcartid == "" || $shoppingcartid == "0" || $shoppingcartid == 0) {
                Log::info('Cart ID is empty, calling cartMaster', [
                    'customerid' => $customerid,
                    'sessionid' => $sessionid,
                    'locale' => $locale
                ]);

                $shippingcountryid = config('app.default_countryid', 1);
                $cartMaster = $this->cartMaster($customerid, $sessionid, $locale, $shippingcountryid);

                Log::info('cartMaster returned', [
                    'cartMaster' => $cartMaster,
                    'is_false' => $cartMaster === false,
                    'has_cartid' => isset($cartMaster['cartid']),
                    'cartid_value' => $cartMaster['cartid'] ?? 'NOT SET'
                ]);

                if ($cartMaster !== false && isset($cartMaster['cartid']) && $cartMaster['cartid'] > 0) {
                    $shoppingcartid = $cartMaster['cartid'];
                    Session::put('shoppingcartid', $shoppingcartid);
                    Log::info('Cart created/retrieved successfully', [
                        'cartid' => $shoppingcartid,
                        'customerid' => $customerid,
                        'sessionid' => $sessionid
                    ]);
                } else {
                    // If cartMaster failed, log error and return 0
                    Log::error('Failed to create/get cart', [
                        'customerid' => $customerid,
                        'sessionid' => $sessionid,
                        'cartMaster' => $cartMaster,
                        'cartMaster_type' => gettype($cartMaster)
                    ]);
                    return response(0);
                }
            } else {
                Log::info('Using existing cart ID', ['cartid' => $shoppingcartid]);
            }

            // Double-check: Never insert with cartid = 0
            if ($shoppingcartid == 0 || $shoppingcartid == "0" || $shoppingcartid == "") {
                Log::error('Invalid cart ID before insert', [
                    'shoppingcartid' => $shoppingcartid,
                    'customerid' => $customerid,
                    'sessionid' => $sessionid
                ]);
                return response(0);
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

            Log::info('Calling cartInsert', [
                'pdata' => $pdata,
                'cartid' => $pdata['cartid']
            ]);

            $returnarr = $this->cartInsert($pdata);

            Log::info('cartInsert returned', [
                'returnarr' => $returnarr,
                'fkcartid' => $returnarr['fkcartid'] ?? 'NOT SET',
                'cnt' => $returnarr['cnt'] ?? 0,
                'status' => $returnarr['status'] ?? false
            ]);

            // Update cart ID in session (cartInsert may have created a new cart)
            if (isset($returnarr['fkcartid']) && $returnarr['fkcartid'] > 0) {
                $shoppingcartid = $returnarr['fkcartid'];
                Session::put('shoppingcartid', $shoppingcartid);
                Log::info('Updated cart ID in session', ['cartid' => $shoppingcartid]);
            }

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

            // Check cart quantities (only if cart ID is valid)
            // NOTE: cartQtyCheck might delete items if they don't have size filters
            // Temporarily disabled to prevent items from being deleted immediately after insert
            // if ($shoppingcartid > 0) {
            //     $this->cartQtyCheck($shoppingcartid);
            // }

            $cart_cnt = $returnarr["cnt"];

            Log::info('=== ADD TO CART END ===', [
                'cart_count' => $cart_cnt,
                'final_cartid' => $shoppingcartid,
                'returnarr' => $returnarr
            ]);

            // Return as plain text (not JSON) to match CI behavior
            return response((string)$cart_cnt)->header('Content-Type', 'text/plain');
        } else {
            $shoppingcartid = Session::get('shoppingcartid');
            if (!empty($shoppingcartid)) {
                $cnt = $this->getCartItemCount($shoppingcartid);
                return response((string)$cnt)->header('Content-Type', 'text/plain');
            } else {
                return response('0')->header('Content-Type', 'text/plain');
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

        // Load cart view only if it exists (for checkout page)
        $cartview = '';
        if (view()->exists('frontend.shoppingcart.cartview')) {
            $cartview = view('frontend.shoppingcart.cartview', [
                'shoppingcartid' => $shoppingcartid
            ])->render();
        }

        // Get updated cart data for overlay
        $cartData = $this->getCartData($shoppingcartid);
        $cartContent = '';
        if ($cartData && $cartData['shoppingcartitems']->count() > 0) {
            $cartContent = view('frontend.shoppingcart.content', [
                'shoppingcart' => $cartData['shoppingcart'],
                'shoppingcartitems' => $cartData['shoppingcartitems'],
                'messages' => DB::table('messages')->get() // Get gift messages
            ])->render();
        } else {
            // Empty cart message
            $cartContent = view('frontend.shoppingcart.content', [
                'shoppingcart' => null,
                'shoppingcartitems' => collect([]),
                'messages' => collect([])
            ])->render();
        }

        return response()->json([
            'status' => true,
            'cnt' => $returnarr['cnt'],
            'cartview' => $cartview,
            'cartcontent' => $cartContent
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
                    ->where('name', 'Gift Message Charge')
                    ->value('details') ?? 0;
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

        // For size=0 (no size), use sum of all sizes as fallback
        $qtyColumn = $sizeid == 0
            ? 'ifnull((select sum(qty) from productsfilter where fkproductid=p.productid and filtercode=\'size\'), 0) as qty'
            : 'ifnull(pf.qty, 0) as qty';

        if ($locale == 'ar') {
            $columns = 'p.shortdescrAR as title, p.productid, p.productcode, p.price, pp.discount, pp.sellingprice, p.photo1, p.titleAR as shortdescr, p.internation_ship, ' . $qtyColumn . ', fv.filtervalue, c.categorycode';
        } else {
            $columns = 'p.shortdescr as title, p.productid, p.productcode, p.price, pp.discount, pp.sellingprice, p.photo1, p.title as shortdescr, p.internation_ship, ' . $qtyColumn . ', fv.filtervalue, c.categorycode';
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
            $productArray = (array) $product;
            // Ensure qty is always a number, not NULL
            if (!isset($productArray['qty']) || $productArray['qty'] === null) {
                $productArray['qty'] = 0;
            }
            return $productArray;
        }

        return false;
    }

    /**
     * Get or create cart master - matches CI cart_master
     */
    public function cartMaster($customerid, $sessionid, $lang, $shippingcountryid)
    {
        Log::info('=== cartMaster START ===', [
            'customerid' => $customerid,
            'sessionid' => $sessionid,
            'lang' => $lang,
            'shippingcountryid' => $shippingcountryid
        ]);

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
                'total' => 0, // Required field, set to 0 initially
                'paymentmethod' => '', // Required field
                'addressid' => 0, // Required field
                'billingaddressid' => 0, // Required field with default
                'shippingaddressid' => 0, // Required field with default
                'couponcode' => '', // Required field
                'couponvalue' => 0, // Required field
                'discount' => 0, // Required field
                'mobiledevice' => '', // Required field
                'browser' => '', // Required field
                'platform' => '', // Required field
            ]);
        } else if ($customerid == 0 && $sessionid != "") {
            Log::info('cartMaster: Guest user with sessionid', ['sessionid' => $sessionid]);

            $cart = DB::table('shoppingcartmaster')
                ->where('sessionid', $sessionid)
                ->where('fkcustomerid', 0)
                ->first();

            if ($cart) {
                $cartid = $cart->cartid;
                Log::info('cartMaster: Found existing cart', ['cartid' => $cartid]);
            } else {
                Log::info('cartMaster: No cart found, creating new one');
                // Create new cart for guest user if none exists
                if ($shippingcountryid == "") {
                    $shippingcountryid = config('app.default_countryid', 1);
                }
                $cartid = DB::table('shoppingcartmaster')->insertGetId([
                    'lang' => $lang,
                    'sessionid' => $sessionid,
                    'fkcustomerid' => $customerid,
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

                Log::info('cartMaster: Created new cart for guest', ['cartid' => $cartid]);
            }
        } else if ($customerid > 0) {
            Log::info('cartMaster: Logged in user', ['customerid' => $customerid]);
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
                    'total' => 0, // Required field, set to 0 initially
                    'paymentmethod' => '', // Required field
                    'addressid' => 0, // Required field
                    'billingaddressid' => 0, // Required field with default
                    'shippingaddressid' => 0, // Required field with default
                    'couponcode' => '', // Required field
                    'couponvalue' => 0, // Required field
                    'discount' => 0, // Required field
                    'mobiledevice' => '', // Required field
                    'browser' => '', // Required field
                    'platform' => '', // Required field
                ]);
            }
        }

        if ($cartid == false) {
            Log::error('cartMaster: cartid is false, returning false');
            return false;
        }

        Log::info('cartMaster: cartid obtained', ['cartid' => $cartid]);

        // Calculate total - use LEFT JOIN to handle empty carts and items without sizes
        $total = DB::table('shoppingcartitems as s')
            ->select(DB::raw('ifnull(sum(s.sellingprice*s.qty),0) as totalamt'))
            ->join('products as p', 'p.productid', '=', 's.fkproductid')
            ->leftJoin('productsfilter as pfsize', function ($join) {
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
            Log::info('=== cartMaster END (SUCCESS) ===', [
                'cartid' => $cartMaster->cartid ?? 'NOT SET',
                'itemtotal' => $cartMaster->itemtotal ?? 0
            ]);
            return (array) $cartMaster;
        }

        Log::error('=== cartMaster END (FAILED) ===', [
            'cartid' => $cartid,
            'cartMaster' => $cartMaster
        ]);

        return false;
    }

    /**
     * Insert cart item - matches CI cart_insert
     */
    protected function cartInsert($pdata)
    {
        Log::info('=== cartInsert START ===', [
            'pdata' => $pdata,
            'cartid' => $pdata['cartid'] ?? 'NOT SET'
        ]);

        $fkproductid = $pdata["id"];
        $cartid = $pdata["cartid"];

        // Safety check: Never insert with cartid = 0
        if ($cartid == 0 || $cartid == "0" || $cartid == "") {
            Log::error('cartInsert called with invalid cartid', [
                'cartid' => $cartid,
                'productid' => $fkproductid,
                'pdata' => $pdata
            ]);
            return [
                'status' => false,
                'msg' => 'Invalid cart ID',
                'cnt' => 0,
                'fkcartid' => 0
            ];
        }

        Log::info('cartInsert: Valid cart ID', ['cartid' => $cartid, 'productid' => $fkproductid]);

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
        // Normalize values for comparison
        $giftmessageNormalized = $giftmessage ?: '';
        $sizeNormalized = (int)$size; // Ensure size is integer for comparison

        // Build query with proper NULL/empty handling for giftmessage
        $existingItemQuery = DB::table('shoppingcartitems')
            ->where('fkproductid', $fkproductid)
            ->where('size', $sizeNormalized) // Use normalized size
            ->where('fkcartid', $cartid)
            ->where('giftmessageid', $giftmessageid);

        // Handle giftmessage comparison (NULL or empty string should match)
        if ($giftmessageNormalized === '') {
            $existingItemQuery->where(function ($query) {
                $query->whereNull('giftmessage')
                    ->orWhere('giftmessage', '');
            });
        } else {
            $existingItemQuery->where('giftmessage', $giftmessageNormalized);
        }

        $existingItem = $existingItemQuery->first();

        Log::info('cartInsert: Checking for existing item', [
            'productid' => $fkproductid,
            'size' => $size,
            'size_normalized' => $sizeNormalized,
            'cartid' => $cartid,
            'giftmessageid' => $giftmessageid,
            'giftmessage' => $giftmessage,
            'giftmessage_normalized' => $giftmessageNormalized,
            'existing_item_found' => $existingItem ? true : false,
            'existing_cartitemid' => $existingItem->cartitemid ?? 'NOT FOUND',
            'existing_item_qty' => $existingItem->qty ?? 'N/A'
        ]);

        $cartitemid = 0;
        if ($existingItem) {
            $cartitemid = $existingItem->cartitemid;
        }

        $status = true;

        if ($cartitemid == 0) {
            // Insert new item
            // Prepare insert data
            $insertData = [
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
            ];

            Log::info('cartInsert: Inserting new item', [
                'cartid' => $cartid,
                'cartid_type' => gettype($cartid),
                'cartid_value' => $cartid,
                'fkcartid_in_insert' => $insertData['fkcartid'],
                'productid' => $fkproductid,
                'size' => $size,
                'qty' => $qty,
                'insert_data_fkcartid' => $insertData['fkcartid']
            ]);

            $insertedId = DB::table('shoppingcartitems')->insertGetId($insertData);

            // Verify what was actually inserted
            $insertedItem = DB::table('shoppingcartitems')
                ->where('cartitemid', $insertedId)
                ->first(['cartitemid', 'fkproductid', 'fkcartid', 'qty']);

            Log::info('cartInsert: Item inserted successfully', [
                'cartid' => $cartid,
                'productid' => $fkproductid,
                'inserted_cartitemid' => $insertedId,
                'verified_fkcartid' => $insertedItem->fkcartid ?? 'NOT FOUND',
                'verified_productid' => $insertedItem->fkproductid ?? 'NOT FOUND',
                'verified_qty' => $insertedItem->qty ?? 'NOT FOUND'
            ]);

            $msg = __('Product added in cart');
        } else {
            // Update existing item
            $pid = $existingItem->fkproductid;
            $size = $existingItem->size;
            $cartqty = $existingItem->qty;
            $qty = $qty + $cartqty;

            Log::info('cartInsert: Item exists, updating quantity', [
                'cartitemid' => $cartitemid,
                'current_qty' => $cartqty,
                'new_qty' => $qty
            ]);

            $product = $this->productBySize($pid, $size);

            // Get available qty (ensure it's a number)
            $availableQty = isset($product['qty']) && $product['qty'] !== null ? (int)$product['qty'] : null;

            Log::info('cartInsert: Checking stock availability', [
                'productid' => $pid,
                'size' => $size,
                'requested_qty' => $qty,
                'available_qty' => $availableQty,
                'product_qty_raw' => $product['qty'] ?? 'NULL',
                'is_update' => true
            ]);

            // Only check stock if we have a valid available quantity
            // If availableQty is null/0, allow the update (item already in cart)
            if ($availableQty !== null && $availableQty > 0 && $qty > $availableQty) {
                $msg = __("Only") . " " . $availableQty . " " . __("available.");
                $status = false;
                Log::info('cartInsert: Stock check failed', [
                    'requested_qty' => $qty,
                    'available_qty' => $availableQty
                ]);
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

        // Use fresh query to ensure we get the latest data (no caching)
        // Recalculate count after insert/update to ensure accuracy
        $cnt = DB::table('shoppingcartitems')
            ->where('fkcartid', $cartid)
            ->count();

        Log::info('cartInsert: Recalculated cart count', [
            'cartid' => $cartid,
            'count' => $cnt,
            'action' => isset($insertedId) ? 'inserted' : 'updated'
        ]);

        // Also verify the specific item we just inserted
        if (isset($insertedId)) {
            $verifyItem = DB::table('shoppingcartitems')
                ->where('cartitemid', $insertedId)
                ->first(['cartitemid', 'fkproductid', 'fkcartid', 'qty']);

            Log::info('cartInsert: Final verification of inserted item', [
                'inserted_cartitemid' => $insertedId,
                'verified_item' => $verifyItem ? [
                    'cartitemid' => $verifyItem->cartitemid,
                    'fkproductid' => $verifyItem->fkproductid,
                    'fkcartid' => $verifyItem->fkcartid,
                    'qty' => $verifyItem->qty
                ] : 'ITEM NOT FOUND IN DB'
            ]);
        }

        Log::info('=== cartInsert END ===', [
            'cartid' => $cartid,
            'item_count' => $cnt,
            'status' => $status,
            'msg' => $msg,
            'inserted_cartitemid' => $insertedId ?? 'NOT SET'
        ]);

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
        if (empty($cartid) || $cartid == 0) {
            return true; // Skip if cart ID is invalid
        }

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
            ->select(DB::raw($columns . ', s.cartitemid, p.productid, s.qty, filtersize.filtervalueid as size, pfsize.qty as maxqty'))
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

        if ($action == 'add' && $productId) {
            // Check if already in wishlist
            $existing = DB::table('wishlist')
                ->where('fkcustomerid', $customerId)
                ->where('fkproductid', $productId)
                ->first();

            if (!$existing) {
                // Add to wishlist
                DB::table('wishlist')->insert([
                    'fkcustomerid' => $customerId,
                    'fkproductid' => $productId,
                ]);
            }

            // Get updated count
            $count = DB::table('wishlist')
                ->where('fkcustomerid', $customerId)
                ->count();

            Session::put('wishlist_item_cnt', $count);

            // Return count as string (CI returns it as string/number)
            return response($count);
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
