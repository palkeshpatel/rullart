<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class CheckoutRepository
{
    /**
     * Get cart items for checkout
     */
    public function getCartItems($shoppingCartId, $locale)
    {
        $titleColumn = $locale == 'ar' ? 'p.shortdescrAR as title' : 'p.shortdescr AS title';
        $shortDescrColumn = $locale == 'ar' ? 'p.titleAR as shortdescr' : 'p.title as shortdescr';

        return DB::table('shoppingcartitems as sci')
            ->select([
                'sci.*',
                'p.productcode',
                DB::raw($titleColumn),
                DB::raw($shortDescrColumn),
                'p.photo1',
                'p.price',
                'c.categorycode',
            ])
            ->leftJoin('products as p', 'sci.fkproductid', '=', 'p.productid')
            ->leftJoin('category as c', 'p.fkcategoryid', '=', 'c.categoryid')
            ->where('sci.fkcartid', $shoppingCartId)
            ->get();
    }
}
