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
        if ($locale == 'ar') {
            return HomeGallery::select(
                DB::raw('titleAR as title'),
                DB::raw('descrAR as descr'),
                'link',
                DB::raw('photo_ar as photo'),
                DB::raw('photo_mobile_ar as photo_mobile'),
                'displayorder',
                DB::raw("IFNULL(videourl, '') as videourl")
            )
                ->where('ispublished', 1)
                ->orderBy('displayorder', 'asc')
                ->get();
        } else {
            return HomeGallery::select(
                'title',
                'titleAR',
                'descr',
                'descrAR',
                'link',
                'photo',
                'photo_mobile',
                'displayorder',
                DB::raw("IFNULL(videourl, '') as videourl")
            )
                ->where('ispublished', 1)
                ->orderBy('displayorder', 'asc')
                ->get();
        }
    }

    /**
     * Get popular products
     */
    public function getPopularProducts($locale)
    {
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

        // Check if productpriceview exists (it's a MySQL view)
        $hasProductPriceView = DB::getSchemaBuilder()->hasTable('productpriceview');
        
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
            ->where('p.ispopular', 1)
            ->where('c.ispublished', 1)
            ->where('pf.qty', '>', 0);

        // Join productpriceview if it exists (MySQL view)
        if ($hasProductPriceView) {
            $column = \App\Helpers\TenantHelper::getProductPriceViewColumn();
            $query->leftJoin('productpriceview as pp', "pp.{$column}", '=', 'p.productid');
        }

        return $query->inRandomOrder()
            ->limit(16)
            ->get();
    }
}

