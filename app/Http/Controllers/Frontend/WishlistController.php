<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class WishlistController extends FrontendController
{
    public function index()
    {
        $locale = app()->getLocale();
        $customerId = Session::get('customerid');
        
        if (!$customerId) {
            return redirect()->route('frontend.login', ['locale' => $locale]);
        }
        
        $wishlistItems = DB::table('wishlist as w')
            ->select([
                'w.wishlistid',
                'p.productid',
                'p.productcode',
                'p.shortdescr',
                'p.shortdescrAR',
                'p.title',
                'p.titleAR',
                'p.photo1',
                'p.price',
                'c.categorycode',
            ])
            ->leftJoin('products as p', 'w.fkproductid', '=', 'p.productid')
            ->leftJoin('category as c', 'p.fkcategoryid', '=', 'c.categoryid')
            ->where('w.fkcustomerid', $customerId)
            ->where('p.ispublished', 1)
            ->get();
        
        return view('frontend.wishlist.index', [
            'wishlistItems' => $wishlistItems
        ]);
    }
    
    public function add(Request $request)
    {
        $productId = $request->input('product_id');
        $customerId = Session::get('customerid');
        
        if (!$customerId) {
            return response()->json([
                'status' => false,
                'msg' => __('Please login to add items to wishlist')
            ]);
        }
        
        // Check if already in wishlist
        $exists = DB::table('wishlist')
            ->where('fkcustomerid', $customerId)
            ->where('fkproductid', $productId)
            ->exists();
        
        if ($exists) {
            return response()->json([
                'status' => false,
                'msg' => __('Item already in wishlist')
            ]);
        }
        
        DB::table('wishlist')->insert([
            'fkcustomerid' => $customerId,
            'fkproductid' => $productId,
            'createddate' => now()
        ]);
        
        $count = DB::table('wishlist')
            ->where('fkcustomerid', $customerId)
            ->count();
        
        Session::put('wishlist_item_cnt', $count);
        
        return response()->json([
            'status' => true,
            'count' => $count,
            'msg' => __('Item added to wishlist')
        ]);
    }
    
    public function remove(Request $request)
    {
        $wishlistId = $request->input('wishlist_id');
        $customerId = Session::get('customerid');
        
        DB::table('wishlist')
            ->where('wishlistid', $wishlistId)
            ->where('fkcustomerid', $customerId)
            ->delete();
        
        $count = DB::table('wishlist')
            ->where('fkcustomerid', $customerId)
            ->count();
        
        Session::put('wishlist_item_cnt', $count);
        
        return response()->json([
            'status' => true,
            'count' => $count
        ]);
    }
}

