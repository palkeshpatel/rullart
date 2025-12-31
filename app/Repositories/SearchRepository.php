<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class SearchRepository
{
    /**
     * Search products
     */
    public function searchProducts($keyword, $categorycode, $color, $size, $price, $sortby, $page, $locale, $currencyRate, $perPage = 20)
    {
        $offset = ($page - 1) * $perPage;
        
        $query = DB::table('products as p')
            ->select([
                'p.productid',
                $locale == 'ar' ? 'p.shortdescrAR as title' : 'p.shortdescr AS title',
                $locale == 'ar' ? 'p.titleAR as shortdescr' : 'p.title as shortdescr',
                'p.productcode',
                'p.photo1',
                'c.categorycode',
                DB::raw("(select sum(qty) from productsfilter where fkproductid=p.productid and productsfilter.filtercode='size') as qty")
            ])
            ->join('category as c', 'p.fkcategoryid', '=', 'c.categoryid')
            ->where('p.ispublished', 1)
            ->where('c.ispublished', 1);
        
        // Search in title and description
        $query->where(function($q) use ($keyword, $locale) {
            $q->where($locale == 'ar' ? 'p.shortdescrAR' : 'p.shortdescr', 'like', '%' . $keyword . '%')
              ->orWhere($locale == 'ar' ? 'p.titleAR' : 'p.title', 'like', '%' . $keyword . '%')
              ->orWhere($locale == 'ar' ? 'p.longdescrAR' : 'p.longdescr', 'like', '%' . $keyword . '%')
              ->orWhere('p.productcode', 'like', '%' . $keyword . '%');
        });
        
        // Filter by category if specified
        if ($categorycode) {
            $query->where('c.categorycode', $categorycode);
        }
        
        // Apply filters
        if ($color) {
            $query->join('productsfilter as pf_color', function($join) use ($color) {
                $join->on('p.productid', '=', 'pf_color.fkproductid')
                     ->where('pf_color.filtercode', '=', 'color')
                     ->where('pf_color.filtervaluecode', '=', $color);
            });
        }
        
        if ($size) {
            $query->join('productsfilter as pf_size', function($join) use ($size) {
                $join->on('p.productid', '=', 'pf_size.fkproductid')
                     ->where('pf_size.filtercode', '=', 'size')
                     ->where('pf_size.filtervaluecode', '=', $size)
                     ->where('pf_size.qty', '>', 0);
            });
        }
        
        if ($price) {
            $priceRange = explode('-', $price);
            if (count($priceRange) == 2) {
                $minPrice = $priceRange[0] / $currencyRate;
                $maxPrice = $priceRange[1] / $currencyRate;
                $query->whereBetween('p.price', [$minPrice, $maxPrice]);
            }
        }
        
        // Check if productpriceview exists (MySQL view)
        $hasProductPriceView = DB::getSchemaBuilder()->hasTable('productpriceview');
        
        if ($hasProductPriceView) {
            $query->leftJoin('productpriceview as pp', 'pp.kproductid', '=', 'p.productid')
                ->addSelect(['pp.discount', 'pp.sellingprice', 'p.price']);
        } else {
            $query->addSelect([
                DB::raw('COALESCE(p.discount, 0) as discount'),
                DB::raw('COALESCE(p.sellingprice, p.price) as sellingprice'),
                'p.price'
            ]);
        }
        
        // Apply sorting
        switch ($sortby) {
            case 'lowtohigh':
                $query->orderBy('p.price', 'asc');
                break;
            case 'hightolow':
                $query->orderBy('p.price', 'desc');
                break;
            case 'name':
                $query->orderBy($locale == 'ar' ? 'p.shortdescrAR' : 'p.shortdescr', 'asc');
                break;
            default: // relevance
                $query->orderBy('p.productid', 'desc');
        }
        
        $total = $query->count();
        $products = $query->havingRaw('qty > 0 OR qty IS NULL')
            ->offset($offset)
            ->limit($perPage)
            ->get();
        
        return [
            'products' => $products,
            'productcnt' => $total,
            'totalpage' => ceil($total / $perPage),
            'colorsArr' => [],
            'sizesArr' => [],
            'pricerange' => [],
        ];
    }

    /**
     * Get subcategories for search results
     */
    public function getSearchSubcategories($locale)
    {
        return DB::table('category as c')
            ->select([
                $locale == 'ar' ? 'c.categoryAR as category' : 'c.category',
                'c.categoryid',
                'c.categorycode',
                'c.parentid',
                DB::raw("(SELECT COUNT(*) FROM products WHERE ispublished=1 AND fkcategoryid=c.categoryid) as productcnt")
            ])
            ->distinct()
            ->join('products as p', 'p.fkcategoryid', '=', 'c.categoryid')
            ->where('p.ispublished', 1)
            ->where('c.ispublished', 1)
            ->orderBy('c.parentid')
            ->orderBy('c.displayorder')
            ->get();
    }
}

