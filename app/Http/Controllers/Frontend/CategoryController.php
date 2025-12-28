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
        
        $products = $this->getCategoryProducts($categoryCode, $locale);
        
        $metaTitle = $locale == 'ar' ? $category->categoryAR : $category->category;
        $metaDescription = '';
        
        return view('frontend.category.index', compact(
            'category',
            'products',
            'metaTitle',
            'metaDescription'
        ));
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
    
    protected function getCategoryProducts($categoryCode, $locale)
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
            ->where('c.categorycode', $categoryCode)
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

