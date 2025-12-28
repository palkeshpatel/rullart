<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends FrontendController
{
    public function index(Request $request)
    {
        $locale = app()->getLocale();
        $keyword = $request->get('keyword', '');
        
        if (empty($keyword)) {
            return redirect()->route('home', ['locale' => $locale]);
        }
        
        // Get filter parameters
        $sortby = $request->get('sortby', 'relevance');
        $color = $request->get('color', '');
        $size = $request->get('size', '');
        $price = $request->get('price', '');
        $categorycode = $request->get('category', '');
        $page = $request->get('page', 1);
        
        // Search products
        $collections = $this->searchProducts($keyword, $categorycode, $color, $size, $price, $sortby, $page, $locale);
        
        $metaTitle = ($this->settingsArr['Website Title'] ?? 'Rullart') . ' : Search Result - ' . $keyword;
        $metaDescription = 'Search results for: ' . $keyword;
        
        $data = [
            'collections' => $collections,
            'search' => $keyword,
            'categorycode' => $categorycode,
            'sortby' => $sortby,
            'color' => $color,
            'size' => $size,
            'price' => $price,
            'page' => $page,
            'metaTitle' => $metaTitle,
            'metaDescription' => $metaDescription,
        ];
        
        return view('frontend.search.index', $data);
    }
    
    protected function searchProducts($keyword, $categorycode, $color, $size, $price, $sortby, $page, $locale)
    {
        $perPage = 20;
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
                $minPrice = $priceRange[0] / $this->currencyRate;
                $maxPrice = $priceRange[1] / $this->currencyRate;
                $query->whereBetween('p.price', [$minPrice, $maxPrice]);
            }
        }
        
        if (DB::getSchemaBuilder()->hasTable('productpriceview')) {
            $query->leftJoin('productpriceview as pp', 'pp.fkproductid', '=', 'p.productid')
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
}

