<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class ProductRepository
{
    /**
     * Get product data by product code
     */
    public function getProductData($productCode, $locale)
    {
        if ($locale == 'ar') {
            $columns = 'p.shortdescrAR as title, p.titleAR as shortdescr, p.longdescrAR as longdescr, c.categoryAR as category, c.categorycode';
        } else {
            $columns = 'p.shortdescr AS title, p.title as shortdescr, p.longdescr, c.category, c.categorycode';
        }

        $query = DB::table('products as p')
            ->select(DB::raw($columns . ', IFNULL(p.video, \'\') as video, IFNULL(p.videoposter, \'\') as videoposter, c.categoryid, p.productid, p.productcode, p.productcategoryid, p.productcategoryid2, p.productcategoryid3, p.productcategoryid4, p.price, p.productid, pp.discount, pp.sellingprice, p.photo1, p.photo2, p.photo3, p.photo4, p.photo5, p.metakeyword, p.metadescr, p.metatitle, c.categorycode, c.parentid, p.internation_ship, IFNULL((SELECT SUM(qty) FROM productsfilter WHERE fkproductid=p.productid AND productsfilter.filtercode=\'size\'), 0) as qty, p.related_category_1, p.related_category_2, p.gift_type, p.related_products'))
            ->join('productpriceview as pp', 'pp.kproductid', '=', 'p.productid')
            ->join('category as c', 'p.fkcategoryid', '=', 'c.categoryid')
            ->where('p.productcode', $productCode)
            ->where('p.ispublished', 1)
            ->where('c.ispublished', 1);

        $product = $query->first();

        if (!$product) {
            return null;
        }

        // Handle videoposter
        if (empty($product->videoposter) && !empty($product->video)) {
            $product->videoposter = 'playvideo.png';
        }

        // Set default photo1 if empty
        if (empty($product->photo1)) {
            $product->photo1 = 'noimage.jpg';
        }

        return $product;
    }

    /**
     * Get product sizes/filters
     */
    public function getProductSizes($productId, $locale)
    {
        if ($locale == 'ar') {
            $columns = 'fv.filtervalueAR as filtervalue';
        } else {
            $columns = 'fv.filtervalue';
        }

        return DB::table('productsfilter as pf')
            ->select(DB::raw($columns . ', pf.filtercode, fv.filtervaluecode, fv.filtervalueid, pf.qty'))
            ->join('filtervalues as fv', 'fv.filtervalueid', '=', 'pf.fkfiltervalueid')
            ->where('fv.fkfilterid', 3)
            ->where('fv.isactive', 1)
            ->where('pf.filtercode', 'size')
            ->where('pf.fkproductid', $productId)
            ->where('pf.qty', '>', 0)
            ->where('fv.filtervalueid', '!=', 0)
            ->orderBy('fv.displayorder', 'asc')
            ->get();
    }

    /**
     * Get related products
     */
    public function getRelatedProducts($product, $locale, $customerId = 0, $limit = 4)
    {
        if ($locale == 'ar') {
            $columns = 'p.shortdescrAR as title';
        } else {
            $columns = 'p.shortdescr as title';
        }

        $select = $columns . ', p.productid, p.productcode, p.price, pp.discount, pp.sellingprice, p.photo1, c.categorycode, (SELECT SUM(qty) FROM productsfilter WHERE fkproductid=p.productid AND productsfilter.filtercode=\'size\') as qty';

        if ($customerId > 0) {
            $select .= ', IFNULL(w.wishlistid, 0) as wishlistid';
        } else {
            $select .= ', 0 as wishlistid';
        }

        $query = DB::table('products as p')
            ->select(DB::raw($select))
            ->distinct()
            ->join('productpriceview as pp', 'pp.kproductid', '=', 'p.productid')
            ->join('category as c', 'p.fkcategoryid', '=', 'c.categoryid');

        if ($customerId > 0) {
            $query->leftJoin('wishlist as w', function ($join) use ($customerId) {
                $join->on('p.productid', '=', 'w.fkproductid')
                    ->where('w.fkcustomerid', '=', $customerId);
            });
        }

        return $query->where('p.ispublished', 1)
            ->where('p.productid', '!=', $product->productid)
            ->whereRaw('(SELECT SUM(qty) FROM productsfilter pf WHERE pf.fkproductid=p.productid) > 0')
            ->where('c.categorycode', $product->categorycode)
            ->limit($limit)
            ->orderByRaw('RAND()')
            ->get();
    }

    /**
     * Get product by size for cart operations
     */
    public function getProductBySize($productId, $size, $locale)
    {
        $localeColumn = $locale == 'ar' ? 'shortdescrAR' : 'shortdescr';
        
        $query = DB::table('products as p')
            ->select([
                'p.productid',
                'p.productcode',
                'p.price',
                DB::raw("p.{$localeColumn} as title"),
                'p.photo1',
                'p.internation_ship',
                DB::raw("ifnull(pf.qty, 0) as qty"),
                'p.discount',
                'p.sellingprice'
            ])
            ->leftJoin('productsfilter as pf', function ($join) use ($size) {
                $join->on('p.productid', '=', 'pf.fkproductid')
                    ->where('pf.filtercode', '=', 'size')
                    ->where('pf.fkfiltervalueid', '=', $size);
            })
            ->where('p.productid', $productId)
            ->where('p.ispublished', 1);

        $product = $query->first();

        // If size=0 and no qty found, sum all sizes
        if ($size == 0 && (empty($product->qty) || $product->qty == 0)) {
            $totalQty = DB::table('productsfilter')
                ->where('fkproductid', $productId)
                ->where('filtercode', 'size')
                ->sum('qty');
            
            if ($product) {
                $product->qty = $totalQty ?? 0;
            }
        }

        return $product;
    }

    /**
     * Check if productpriceview exists
     */
    public function hasProductPriceView()
    {
        try {
            $result = DB::selectOne("
                SELECT 1
                FROM information_schema.views
                WHERE table_schema = DATABASE()
                  AND table_name = 'productpriceview'
            ");
            return !empty($result);
        } catch (\Exception $e) {
            return false;
        }
    }
}

