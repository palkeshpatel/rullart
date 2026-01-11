<?php

namespace App\Repositories;

use App\Models\HomeGallery;
use Illuminate\Support\Facades\DB;

class HomeRepository
{
    /**
     * Get home gallery
     */
    public function getHomeGallery($locale)
    {
        // Always select both English and Arabic fields, then use based on locale in view
        return HomeGallery::select(
            'title',
            'titleAR',
            'descr',
            'descrAR',
            'link',
            'photo',
            'photo_ar',
            'photo_mobile',
            'photo_mobile_ar',
            'displayorder',
            DB::raw("IFNULL(videourl, '') as videourl")
        )
            ->where('ispublished', 1)
            ->orderBy('displayorder', 'asc')
            ->get();
    }

    /**
     * Get popular products
     * Matches CI project logic: Get one product per distinct category (up to 16 categories)
     */
    public function getPopularProducts($locale)
    {
        // Step 1: Get distinct category IDs that have popular products (matching CI logic)
        $categoryIds = DB::table('products as p')
            ->select('p.fkcategoryid')
            ->distinct()
            ->join('category as c', 'p.fkcategoryid', '=', 'c.categoryid')
            ->join('productsfilter as pf', function ($join) {
                $join->on('p.productid', '=', 'pf.fkproductid')
                    ->where('pf.filtercode', '=', 'size');
            })
            ->where('p.ispublished', 1)
            ->where('c.ispublished', 1)
            ->where('pf.qty', '>', 0)
            ->where('p.ispopular', 1)
            ->inRandomOrder()
            ->limit(16)
            ->pluck('fkcategoryid')
            ->toArray();

        if (empty($categoryIds)) {
            return collect([]);
        }

        // Step 2: For each category, get one random product (matching CI logic)
        $products = [];
        $hasProductPriceView = DB::getSchemaBuilder()->hasTable('productpriceview');
        
        foreach ($categoryIds as $categoryId) {
            $selectFields = [
                'p.productid',
                $locale == 'ar' ? 'p.shortdescrAR as title' : 'p.shortdescr AS title',
                'p.productcode',
                'p.price',
                'p.photo1',
                $locale == 'ar' ? 'p.titleAR as shortdescr' : 'p.title as shortdescr',
                'c.categorycode',
                DB::raw("(select sum(qty) from productsfilter where fkproductid=p.productid and productsfilter.filtercode='size') as qty")
            ];

            if ($hasProductPriceView) {
                $selectFields[] = 'pp.discount';
                $selectFields[] = 'pp.sellingprice';
            } else {
                $selectFields[] = DB::raw('COALESCE(p.discount, 0) as discount');
                $selectFields[] = DB::raw('COALESCE(p.sellingprice, p.price) as sellingprice');
            }

            $query = DB::table('products as p')
                ->select($selectFields)
                ->join('category as c', 'p.fkcategoryid', '=', 'c.categoryid')
                ->join('productsfilter as pf', function ($join) {
                    $join->on('p.productid', '=', 'pf.fkproductid')
                        ->where('pf.filtercode', '=', 'size');
                })
                ->where('p.ispublished', 1)
                ->where('c.ispublished', 1)
                ->where('p.ispopular', 1)
                ->where('pf.qty', '>', 0)
                ->where('p.fkcategoryid', $categoryId);

            // Join productpriceview if it exists (MySQL view)
            if ($hasProductPriceView) {
                $column = \App\Helpers\TenantHelper::getProductPriceViewColumn();
                $query->leftJoin('productpriceview as pp', "pp.{$column}", '=', 'p.productid');
            }

            $product = $query->inRandomOrder()
                ->limit(1)
                ->first();

            if ($product) {
                $products[] = $product;
            }
        }

        return collect($products);
    }
}

