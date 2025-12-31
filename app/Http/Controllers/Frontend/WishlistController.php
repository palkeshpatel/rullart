<?php

namespace App\Http\Controllers\Frontend;

use App\Repositories\WishlistRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;

class WishlistController extends FrontendController
{
    protected $wishlistRepository;

    public function __construct(WishlistRepository $wishlistRepository)
    {
        $this->wishlistRepository = $wishlistRepository;
    }

    public function index()
    {
        try {
            $locale = app()->getLocale();
            $customerId = Session::get('customerid');
            $customerIdInt = $customerId ? (int) $customerId : 0;

            Log::info("WishlistController: Loading wishlist for customer ID: {$customerIdInt}");

            $wishlistItems = $this->wishlistRepository->getWishlistItems($customerIdInt, $locale);

            Log::info("WishlistController: Found " . $wishlistItems->count() . " wishlist items");

            $resourceUrl = $this->resourceUrl ?? url('/resources/') . '/';

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

        $added = $this->wishlistRepository->addToWishlist($customerId, $productId);

        if (!$added) {
            return response()->json([
                'status' => false,
                'msg' => __('Item already in wishlist')
            ]);
        }

        $count = $this->wishlistRepository->getWishlistCount($customerId);
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

        $this->wishlistRepository->removeFromWishlist($wishlistId, $customerId);

        $count = $this->wishlistRepository->getWishlistCount($customerId);
        Session::put('wishlist_item_cnt', $count);

        return response()->json([
            'status' => true,
            'count' => $count
        ]);
    }
}