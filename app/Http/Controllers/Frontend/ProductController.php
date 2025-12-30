<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends FrontendController
{
    public function show($locale, $category, $product)
    {
        $customerId = session('customerid', 0);

        // Match CI Product->index() structure
        $productData = $this->getProductData($product, $locale);

        if (!$productData) {
            abort(404, 'Product not found');
        }

        // Check if category matches (CI line 94-96)
        if ($category != $productData->categorycode) {
            abort(404, 'Category mismatch');
        }

        // Get product photos array (match CI lines 77-93)
        $photos = $this->getProductPhotos($productData);

        // Get product sizes/filters (match CI lines 95-112)
        $sizes = $this->getProductSizes($productData->productid, $locale);

        // Get wishlist status (match CI lines 123-134)
        $wishlistcnt = 0;
        $wishlistid = 0;
        if ($customerId > 0) {
            $wishlist = DB::table('wishlist')
                ->where('fkcustomerid', $customerId)
                ->where('fkproductid', $productData->productid)
                ->first();
            if ($wishlist) {
                $wishlistcnt = 1;
                $wishlistid = $wishlist->wishlistid;
            }
        }
        $productData->wishlistid = $wishlistid;

        // Get parent category (match CI lines 136-148)
        $parentcategory = '';
        $parentcategorycode = '';
        $categoryArr = null;
        if ($productData->parentid > 0) {
            $categoryArr = DB::table('category')
                ->where('categoryid', $productData->parentid)
                ->where('ispublished', 1)
                ->first();
            if ($categoryArr) {
                $parentcategory = $locale == 'ar' ? $categoryArr->categoryAR : $categoryArr->category;
                $parentcategorycode = $categoryArr->categorycode;
            }
        }

        // Get related products (match CI lines 150-201)
        $relatedProducts = $this->getRelatedProducts($productData, $locale, $customerId);

        // Get Delivery & Returns (match CI lines 229-233)
        $deliveryReturns = '';
        if ($locale == 'ar') {
            $deliveryReturns = $this->settingsArr['Delivery & Returns (AR)'] ?? '';
        } else {
            $deliveryReturns = $this->settingsArr['Delivery & Returns'] ?? '';
        }

        // Get gift messages if enabled (match CI lines 84-85, 258-268)
        $messages = collect([]);
        $showGiftMessage = false;
        $giftMessageCharge = 0;
        if ($productData->qty > 0) {
            $showGiftMessageSetting = $this->settingsArr['Show Gift Message'] ?? 'No';
            if ($showGiftMessageSetting == 'Yes') {
                $messages = DB::table('messages')->get();
                if ($messages->count() > 0) {
                    $showGiftMessage = true;
                    $giftMessageCharge = $this->settingsArr['Gift Message Charge'] ?? 0;
                }
            }
        }

        // Check international delivery (match CI lines 21-31)
        $shippingCountry = config('app.default_country', 'Kuwait');
        $defaultCountry = config('app.default_country', 'Kuwait');
        $internationDelivery = true;
        if (isset($productData->internation_ship) && $productData->internation_ship == 0 && $shippingCountry != $defaultCountry) {
            $internationDelivery = false;
        }

        // Get page meta (match CI lines 215-222)
        $metaTitle = $productData->metatitle;
        if (empty($metaTitle)) {
            $metaTitle = $productData->title;
        }
        $metaDescription = $productData->metadescr ?? '';
        $metaKeywords = $productData->metakeyword ?? '';

        return view('frontend.product.show', compact(
            'productData',
            'photos',
            'sizes',
            'relatedProducts',
            'metaTitle',
            'metaDescription',
            'metaKeywords',
            'parentcategory',
            'parentcategorycode',
            'wishlistcnt',
            'wishlistid',
            'deliveryReturns',
            'messages',
            'showGiftMessage',
            'giftMessageCharge',
            'internationDelivery'
        ));
    }

    public function showByCode($locale, $product)
    {

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
        // Match CI Product_model->get_data() exactly
        if ($locale == 'ar') {
            $columns = 'p.shortdescrAR as title, p.titleAR as shortdescr, p.longdescrAR as longdescr, c.categoryAR as category, c.categorycode';
        } else {
            $columns = 'p.shortdescr AS title, p.title as shortdescr, p.longdescr, c.category, c.categorycode';
        }

        $query = DB::table('products as p')
            ->select(DB::raw($columns . ', IFNULL(p.video, \'\') as video, IFNULL(p.videoposter, \'\') as videoposter, c.categoryid, p.productid, p.productcode, p.productcategoryid, p.productcategoryid2, p.productcategoryid3, p.productcategoryid4, p.price, p.productid, pp.discount, pp.sellingprice, p.photo1, p.photo2, p.photo3, p.photo4, p.photo5, p.metakeyword, p.metadescr, p.metatitle, c.categorycode, c.parentid, p.internation_ship, IFNULL((SELECT SUM(qty) FROM productsfilter WHERE fkproductid=p.productid AND productsfilter.filtercode=\'size\'), 0) as qty, p.related_category_1, p.related_category_2, p.gift_type, p.related_products'))
            ->join('productpriceview as pp', 'pp.kproductid', '=', 'p.productid') // Actual view uses kproductid
            ->join('category as c', 'p.fkcategoryid', '=', 'c.categoryid')
            ->where('p.productcode', $productCode)
            ->where('p.ispublished', 1)
            ->where('c.ispublished', 1);

        $product = $query->first();

        if (!$product) {
            return false;
        }

        // Handle videoposter like CI
        if (empty($product->videoposter) && !empty($product->video)) {
            $product->videoposter = 'playvideo.png';
        }

        // Set default photo1 if empty (like CI line 98-100)
        if (empty($product->photo1)) {
            $product->photo1 = 'noimage.jpg';
        }

        return $product;
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
        // Match CI Product_model->get_data() size query exactly (lines 96-112)
        if ($locale == 'ar') {
            $columns = 'fv.filtervalueAR as filtervalue';
        } else {
            $columns = 'fv.filtervalue';
        }

        $query = DB::table('productsfilter as pf')
            ->select(DB::raw($columns . ', pf.filtercode, fv.filtervaluecode, fv.filtervalueid, pf.qty'))
            ->join('filtervalues as fv', 'fv.filtervalueid', '=', 'pf.fkfiltervalueid')
            ->where('fv.fkfilterid', 3) // CI uses fkfilterid = 3 for size
            ->where('fv.isactive', 1)
            ->where('pf.filtercode', 'size')
            ->where('pf.fkproductid', $productId)
            ->where('pf.qty', '>', 0)
            ->where('fv.filtervalueid', '!=', 0)
            ->orderBy('fv.displayorder', 'asc');

        return $query->get();
    }

    protected function getRelatedProducts($product, $locale, $customerId = 0, $limit = 4)
    {
        // Match CI Product_model->get_related() exactly (lines 221-272)
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
            ->join('productpriceview as pp', 'pp.kproductid', '=', 'p.productid') // Actual view uses kproductid
            ->join('category as c', 'p.fkcategoryid', '=', 'c.categoryid');

        if ($customerId > 0) {
            $query->leftJoin('wishlist as w', function ($join) use ($customerId) {
                $join->on('p.productid', '=', 'w.fkproductid')
                    ->where('w.fkcustomerid', '=', $customerId);
            });
        }

        $query->where('p.ispublished', 1)
            ->where('p.productid', '!=', $product->productid)
            ->whereRaw('(SELECT SUM(qty) FROM productsfilter pf WHERE pf.fkproductid=p.productid) > 0')
            ->where('c.categorycode', $product->categorycode)
            ->limit($limit)
            ->orderByRaw('RAND()'); // CI uses RANDOM

        return $query->get();
    }
}
