<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends FrontendController
{
    public function show($category, $product)
    {
        $locale = app()->getLocale();
        
        $productData = $this->getProductData($product, $locale);
        
        if (!$productData) {
            abort(404, 'Product not found');
        }
        
        // Check if category matches
        if ($category != $productData->categorycode) {
            abort(404, 'Category mismatch');
        }
        
        // Get product photos
        $photos = $this->getProductPhotos($productData);
        
        // Get product sizes/filters if available
        $sizes = $this->getProductSizes($productData->productid, $locale);
        
        // Get related products
        $relatedProducts = $this->getRelatedProducts($productData, $locale);
        
        // Get page meta
        $metaTitle = $productData->metatitle ?? $productData->title;
        $metaDescription = $productData->metadescr ?? strip_tags($productData->shortdescr);
        $metaKeywords = $productData->metakeyword ?? '';
        
        return view('frontend.product.show', compact(
            'productData',
            'photos',
            'sizes',
            'relatedProducts',
            'metaTitle',
            'metaDescription',
            'metaKeywords'
        ));
    }
    
    public function showByCode($product)
    {
        $locale = app()->getLocale();
        
        $productData = $this->getProductData($product, $locale);
        
        if (!$productData) {
            abort(404, 'Product not found');
        }
        
        // Redirect to proper URL with category
        return redirect()->route('product.show', [
            'locale' => $locale,
            'category' => $productData->categorycode,
            'product' => $product
        ]);
    }
    
    protected function getProductData($productCode, $locale)
    {
        $query = DB::table('products as p')
            ->select([
                'p.productid',
                $locale == 'ar' ? 'p.shortdescrAR as title' : 'p.shortdescr AS title',
                $locale == 'ar' ? 'p.titleAR as shortdescr' : 'p.title as shortdescr',
                $locale == 'ar' ? 'p.longdescrAR as longdescr' : 'p.longdescr',
                $locale == 'ar' ? 'c.categoryAR as category' : 'c.category',
                'p.productcode',
                'p.photo1',
                'p.photo2',
                'p.photo3',
                'p.photo4',
                'p.photo5',
                'c.categorycode',
                'c.parentid',
                'p.fkcategoryid',
                'p.metatitle',
                'p.metadescr',
                'p.metakeyword',
                DB::raw("(select sum(qty) from productsfilter where fkproductid=p.productid and productsfilter.filtercode='size') as qty")
            ])
            ->leftJoin('category as c', 'p.fkcategoryid', '=', 'c.categoryid')
            ->where('p.productcode', $productCode)
            ->where('p.ispublished', 1)
            ->where('c.ispublished', 1);
        
        // Try to use productpriceview if it exists
        if (DB::getSchemaBuilder()->hasTable('productpriceview')) {
            $query->leftJoin('productpriceview as pp', 'pp.fkproductid', '=', 'p.productid')
                ->addSelect([
                    'pp.discount',
                    'pp.sellingprice',
                    'p.price'
                ]);
        } else {
            $query->addSelect([
                DB::raw('COALESCE(p.discount, 0) as discount'),
                DB::raw('COALESCE(p.sellingprice, p.price) as sellingprice'),
                'p.price'
            ]);
        }
        
        return $query->first();
    }
    
    protected function getProductPhotos($product)
    {
        $photos = [];
        if (!empty($product->photo1)) $photos[] = $product->photo1;
        if (!empty($product->photo2)) $photos[] = $product->photo2;
        if (!empty($product->photo3)) $photos[] = $product->photo3;
        if (!empty($product->photo4)) $photos[] = $product->photo4;
        if (!empty($product->photo5)) $photos[] = $product->photo5;
        
        return $photos;
    }
    
    protected function getProductSizes($productId, $locale)
    {
        $query = DB::table('productsfilter as pf')
            ->select([
                $locale == 'ar' ? 'fv.filtervalueAR as filtervalue' : 'fv.filtervalue',
                'pf.qty'
            ])
            ->leftJoin('filtervalues as fv', 'pf.fkfiltervalueid', '=', 'fv.filtervalueid')
            ->where('pf.fkproductid', $productId)
            ->where('pf.filtercode', 'size')
            ->where('pf.qty', '>', 0)
            ->orderBy('fv.displayorder', 'asc');
        
        return $query->get();
    }
    
    protected function getRelatedProducts($product, $locale, $limit = 4)
    {
        // Get products from same category
        $query = DB::table('products as p')
            ->select([
                'p.productid',
                $locale == 'ar' ? 'p.shortdescrAR as title' : 'p.shortdescr AS title',
                'p.productcode',
                'p.photo1',
                'c.categorycode',
                DB::raw("(select sum(qty) from productsfilter where fkproductid=p.productid and productsfilter.filtercode='size') as qty")
            ])
            ->leftJoin('category as c', 'p.fkcategoryid', '=', 'c.categoryid')
            ->where('p.fkcategoryid', $product->fkcategoryid)
            ->where('p.productid', '!=', $product->productid)
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
        
        return $query->orderBy('p.productid', 'desc')
            ->limit($limit)
            ->get();
    }
}

