<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends FrontendController
{
    public function index($categoryCode)
    {
        $locale = app()->getLocale();
        
        $category = Category::where('categorycode', $categoryCode)
            ->where('ispublished', 1)
            ->first();
        
        if (!$category) {
            abort(404, 'Category not found');
        }
        
        // Get filter parameters
        $sortby = request()->get('sortby', 'relevance');
        $color = request()->get('color', '');
        $size = request()->get('size', '');
        $price = request()->get('price', '');
        $page = request()->get('page', 1);
        $main = request()->get('main', 0);
        $subcategory = request()->get('category', '');
        
        $collections = $this->getCategoryProducts($categoryCode, $locale, $sortby, $color, $size, $price, $page, $main, $subcategory);
        
        if (!$collections) {
            abort(404, 'Category not found');
        }
        
        // Prepare meta data
        $metaTitle = $locale == 'ar' 
            ? ($this->settingsArr['Website Title'] ?? 'Rullart') . ' : ' . $collections['category']->metatitleAR
            : ($this->settingsArr['Website Title'] ?? 'Rullart') . ' : ' . $collections['category']->metatitle;
        
        $metaDescription = $locale == 'ar' 
            ? $collections['category']->metadescrAR 
            : $collections['category']->metadescr;
        
        $data = [
            'collections' => $collections,
            'category' => $collections['category'],
            'products' => $collections['products'],
            'subcategory' => $collections['subcategory'] ?? [],
            'colorsArr' => $collections['colorsArr'] ?? [],
            'sizesArr' => $collections['sizesArr'] ?? [],
            'pricerange' => $collections['pricerange'] ?? [],
            'productcnt' => $collections['productcnt'] ?? 0,
            'totalpage' => $collections['totalpage'] ?? 1,
            'page' => $page,
            'main' => $main,
            'sortby' => $sortby,
            'color' => $color,
            'size' => $size,
            'price' => $price,
            'metaTitle' => $metaTitle,
            'metaDescription' => $metaDescription,
        ];
        
        // Check if gift category (categoryid == 80)
        if ($collections['category']->categoryid == 80) {
            return view('frontend.category.gift-category', $data);
        }
        
        return view('frontend.category.index', $data);
    }
    
    public function all()
    {
        $locale = app()->getLocale();
        $products = $this->getAllProducts($locale);
        
        return view('frontend.category.index', [
            'category' => null,
            'products' => $products,
            'metaTitle' => __('All Products'),
            'metaDescription' => ''
        ]);
    }
    
    public function occassion($occassionCode)
    {
        $locale = app()->getLocale();
        
        $occassion = DB::table('occassion')
            ->where('occassioncode', $occassionCode)
            ->where('ispublished', 1)
            ->first();
        
        if (!$occassion) {
            abort(404);
        }
        
        // Get products by occasion (you'll need to implement this based on your schema)
        $products = collect([]);
        
        return view('frontend.category.index', [
            'category' => null,
            'products' => $products,
            'metaTitle' => $locale == 'ar' ? $occassion->occassionAR : $occassion->occassion,
            'metaDescription' => ''
        ]);
    }
    
    public function whatsNew()
    {
        $locale = app()->getLocale();
        $products = $this->getNewProducts($locale);
        
        return view('frontend.category.index', [
            'category' => null,
            'products' => $products,
            'metaTitle' => __('What\'s New'),
            'metaDescription' => ''
        ]);
    }
    
    public function sale()
    {
        $locale = app()->getLocale();
        $products = $this->getSaleProducts($locale);
        
        return view('frontend.category.index', [
            'category' => null,
            'products' => $products,
            'metaTitle' => __('Sale'),
            'metaDescription' => ''
        ]);
    }
    
    protected function getCategoryProducts($categoryCode, $locale, $sortby = 'relevance', $color = '', $size = '', $price = '', $page = 1, $main = 0, $subcategory = '')
    {
        $currencyCode = $this->currencyCode;
        $currencyRate = $this->currencyRate;
        $customerId = session('customerid', 0);
        
        // Get category model instance to use its methods
        $categoryModel = new \App\Models\Category();
        
        // Use the category model's get_products method if available, otherwise build query
        if (method_exists($categoryModel, 'getProducts')) {
            return $categoryModel->getProducts($currencyCode, $currencyRate, $customerId, $categoryCode, '', $color, $price, $size, $sortby, $page, $main, 1);
        }
        
        // Fallback: Build query manually
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
            ->where('c.categorycode', $categoryCode)
            ->where('p.ispublished', 1)
            ->where('c.ispublished', 1);
        
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
            case 'price_asc':
                $query->orderBy('p.price', 'asc');
                break;
            case 'price_desc':
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
        
        // Get category info
        $category = Category::where('categorycode', $categoryCode)->first();
        
        // Get subcategories
        $subcategories = Category::where('parentid', $category->categoryid)
            ->where('ispublished', 1)
            ->orderBy('displayorder', 'asc')
            ->get();
        
        return [
            'category' => $category,
            'products' => $products,
            'subcategory' => $subcategories,
            'productcnt' => $total,
            'totalpage' => ceil($total / $perPage),
            'colorsArr' => [],
            'sizesArr' => [],
            'pricerange' => [],
        ];
    
    protected function getAllProducts($locale)
    {
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
            ->leftJoin('category as c', 'p.fkcategoryid', '=', 'c.categoryid')
            ->where('p.ispublished', 1)
            ->where('c.ispublished', 1);
        
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
        
        return $query->havingRaw('qty > 0 OR qty IS NULL')
            ->orderBy('p.productid', 'desc')
            ->paginate(20);
    }
    
    protected function getNewProducts($locale)
    {
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
            ->leftJoin('category as c', 'p.fkcategoryid', '=', 'c.categoryid')
            ->where('p.ispublished', 1)
            ->where('p.isnew', 1)
            ->where('c.ispublished', 1);
        
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
        
        return $query->havingRaw('qty > 0 OR qty IS NULL')
            ->orderBy('p.productid', 'desc')
            ->paginate(20);
    }
    
    protected function getSaleProducts($locale)
    {
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
            ->leftJoin('category as c', 'p.fkcategoryid', '=', 'c.categoryid')
            ->where('p.ispublished', 1)
            ->where('c.ispublished', 1);
        
        if (DB::getSchemaBuilder()->hasTable('productpriceview')) {
            $query->leftJoin('productpriceview as pp', 'pp.fkproductid', '=', 'p.productid')
                ->where('pp.discount', '>', 0)
                ->addSelect(['pp.discount', 'pp.sellingprice', 'p.price']);
        } else {
            $query->where('p.discount', '>', 0)
                ->addSelect([
                    DB::raw('COALESCE(p.discount, 0) as discount'),
                    DB::raw('COALESCE(p.sellingprice, p.price) as sellingprice'),
                    'p.price'
                ]);
        }
        
        return $query->havingRaw('qty > 0 OR qty IS NULL')
            ->orderBy('p.productid', 'desc')
            ->paginate(20);
    }
}

