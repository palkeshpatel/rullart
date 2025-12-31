<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class WishlistRepository
{
    /**
     * Get wishlist items for customer
     */
    public function getWishlistItems($customerId, $locale)
    {
        $titleColumn = $locale == 'ar' ? 'p.shortdescrAR as title' : 'p.shortdescr AS title';

        return DB::table('wishlist as w')
            ->select([
                DB::raw($titleColumn),
                'w.wishlistid',
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
            ->where('w.fkcustomerid', $customerId)
            ->get();
    }

    /**
     * Check if product is in wishlist
     */
    public function isInWishlist($customerId, $productId)
    {
        return DB::table('wishlist')
            ->where('fkcustomerid', $customerId)
            ->where('fkproductid', $productId)
            ->exists();
    }

    /**
     * Add to wishlist
     */
    public function addToWishlist($customerId, $productId)
    {
        // Check if already exists
        $exists = $this->isInWishlist($customerId, $productId);

        if ($exists) {
            return false;
        }

        return DB::table('wishlist')->insert([
            'fkcustomerid' => $customerId,
            'fkproductid' => $productId,
            'createddate' => now()
        ]);
    }

    /**
     * Remove from wishlist
     */
    public function removeFromWishlist($wishlistId, $customerId)
    {
        return DB::table('wishlist')
            ->where('wishlistid', $wishlistId)
            ->where('fkcustomerid', $customerId)
            ->delete();
    }

    /**
     * Remove by product ID
     */
    public function removeByProductId($customerId, $productId)
    {
        return DB::table('wishlist')
            ->where('fkcustomerid', $customerId)
            ->where('fkproductid', $productId)
            ->delete();
    }

    /**
     * Get wishlist count
     */
    public function getWishlistCount($customerId)
    {
        return DB::table('wishlist')
            ->where('fkcustomerid', $customerId)
            ->count();
    }

    /**
     * Get wishlist item
     */
    public function getWishlistItem($customerId, $productId)
    {
        return DB::table('wishlist')
            ->where('fkcustomerid', $customerId)
            ->where('fkproductid', $productId)
            ->first();
    }
}
