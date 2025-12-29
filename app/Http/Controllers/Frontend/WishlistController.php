<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class WishlistController extends FrontendController
{
    public function index()
    {
        try {
            $locale = app()->getLocale();
            // Match CI exactly - get customerid from session (can be null/0)
            // CI doesn't check if logged in, just tries to get wishlist items
            $customerId = Session::get('customerid');

            // Match CI wishlist_get_items() method exactly
            $titleColumn = $locale == 'ar' ? 'p.shortdescrAR as title' : 'p.shortdescr AS title';

            // If customerId is null/0, query will return empty results (which is correct)
            $customerIdInt = $customerId ? (int) $customerId : 0;

            Log::info("WishlistController: Loading wishlist for customer ID: {$customerIdInt}");

            // Match CI exactly - use inner join (not leftJoin)
            // If customerId is 0, this will return empty collection
            $wishlistItems = DB::table('wishlist as w')
                ->select([
                    DB::raw($titleColumn),
                    'w.fkproductid',
                    'p.productcode',
                    'p.photo1',
                    'p.price',
                    'p.discount',
                    'p.sellingprice',
                    'c.categorycode',
                ])
                ->join('products as p', 'p.productid', '=', 'w.fkproductid')
                ->join('category as c', 'p.fkcategoryid', '=', 'c.categoryid')
                ->where('w.fkcustomerid', $customerIdInt)
                // Note: CI doesn't filter by ispublished, so we won't either
                ->get();

            Log::info("WishlistController: Found " . $wishlistItems->count() . " wishlist items");

            // Ensure resourceUrl is available
            $resourceUrl = $this->resourceUrl ?? url('/resources/') . '/';

            // Always return wishlist view (will show "Your Wishlist is empty" if no items)
            return view('frontend.wishlist.index', [
                'locale' => $locale,
                'wishlistItems' => $wishlistItems,
                'resourceUrl' => $resourceUrl,
            ]);
        } catch (\Exception $e) {
            Log::error('WishlistController index error: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            Log::error($e->getTraceAsString());
            return response('<div class="overlay-section"><div class="alert alert-danger">Error loading wishlist. Please try again.</div></div>', 500);
        }
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