<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

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
        
        if (!$cartData['shoppingcart']) {
            Session::forget('shoppingcartid');
            return redirect()->route('home', ['locale' => $locale]);
        }
        
        return view('frontend.shoppingcart.index', $cartData);
    }
    
    protected function getOrCreateCartId($customerId, $sessionId)
    {
        // Check if cart exists
        $cart = DB::table('shoppingcartmaster')
            ->where('fkcustomerid', $customerId)
            ->where('sessionid', $sessionId)
            ->where('isactive', 1)
            ->first();
        
        if ($cart) {
            return $cart->shoppingcartid;
        }
        
        // Create new cart
        $cartId = DB::table('shoppingcartmaster')->insertGetId([
            'fkcustomerid' => $customerId,
            'sessionid' => $sessionId,
            'isactive' => 1,
            'createddate' => now(),
        ]);
        
        return $cartId;
    }
    
    protected function getCartData($shoppingCartId)
    {
        $cart = DB::table('shoppingcartmaster')
            ->where('shoppingcartid', $shoppingCartId)
            ->where('isactive', 1)
            ->first();
        
        if (!$cart) {
            return [
                'shoppingcart' => null,
                'shoppingcartitems' => collect([])
            ];
        }
        
        $items = DB::table('shoppingcartitem as sci')
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
            ->where('sci.fkshoppingcartid', $shoppingCartId)
            ->get();
        
        return [
            'shoppingcart' => $cart,
            'shoppingcartitems' => $items
        ];
    }
    
    public function ajaxCart(Request $request)
    {
        $action = $request->input('action');
        
        switch ($action) {
            case 'add':
                return $this->addToCart($request);
            case 'update':
                return $this->updateCart($request);
            case 'delete':
                return $this->deleteFromCart($request);
            default:
                return response()->json(['status' => false, 'msg' => 'Invalid action']);
        }
    }
    
    protected function addToCart(Request $request)
    {
        $productId = $request->input('p_id');
        $quantity = $request->input('quantity', 1);
        $size = $request->input('size', '');
        
        $shoppingCartId = Session::get('shoppingcartid');
        if (!$shoppingCartId) {
            $customerId = Session::get('customerid', 0);
            $sessionId = Session::getId();
            $shoppingCartId = $this->getOrCreateCartId($customerId, $sessionId);
            Session::put('shoppingcartid', $shoppingCartId);
        }
        
        // Check if item already exists
        $existingItem = DB::table('shoppingcartitem')
            ->where('fkshoppingcartid', $shoppingCartId)
            ->where('fkproductid', $productId)
            ->where('size', $size)
            ->first();
        
        if ($existingItem) {
            // Update quantity
            DB::table('shoppingcartitem')
                ->where('shoppingcartitemid', $existingItem->shoppingcartitemid)
                ->update([
                    'qty' => $existingItem->qty + $quantity,
                    'updateddate' => now()
                ]);
        } else {
            // Add new item
            DB::table('shoppingcartitem')->insert([
                'fkshoppingcartid' => $shoppingCartId,
                'fkproductid' => $productId,
                'qty' => $quantity,
                'size' => $size,
                'createddate' => now()
            ]);
        }
        
        $cartCount = DB::table('shoppingcartitem')
            ->where('fkshoppingcartid', $shoppingCartId)
            ->sum('qty');
        
        return response()->json([
            'status' => true,
            'cnt' => $cartCount,
            'msg' => __('Item added to cart')
        ]);
    }
    
    protected function updateCart(Request $request)
    {
        $rowId = $request->input('rowid');
        $quantity = $request->input('quantity', 1);
        
        DB::table('shoppingcartitem')
            ->where('shoppingcartitemid', $rowId)
            ->update([
                'qty' => $quantity,
                'updateddate' => now()
            ]);
        
        return response()->json(['status' => true]);
    }
    
    protected function deleteFromCart(Request $request)
    {
        $rowId = $request->input('rowid');
        
        DB::table('shoppingcartitem')
            ->where('shoppingcartitemid', $rowId)
            ->delete();
        
        $shoppingCartId = Session::get('shoppingcartid');
        $cartCount = DB::table('shoppingcartitem')
            ->where('fkshoppingcartid', $shoppingCartId)
            ->sum('qty');
        
        return response()->json([
            'status' => true,
            'cnt' => $cartCount
        ]);
    }
}

