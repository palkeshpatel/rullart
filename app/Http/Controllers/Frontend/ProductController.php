<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Product;
use App\Models\Category;
use App\Repositories\ProductRepository;
use App\Repositories\WishlistRepository;
use App\Repositories\ShoppingCartRepository;
use Illuminate\Http\Request;

class ProductController extends FrontendController
{
    protected $productRepository;
    protected $wishlistRepository;
    protected $cartRepository;

    public function __construct(
        ProductRepository $productRepository,
        WishlistRepository $wishlistRepository,
        ShoppingCartRepository $cartRepository
    ) {
        parent::__construct();
        $this->productRepository = $productRepository;
        $this->wishlistRepository = $wishlistRepository;
        $this->cartRepository = $cartRepository;
    }

    public function show($locale, $category, $product)
    {
        $customerId = session('customerid', 0);

        // Match CI Product->index() structure
        $productData = $this->productRepository->getProductData($product, $locale);
        
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
        $sizes = $this->productRepository->getProductSizes($productData->productid, $locale);

        // Get wishlist status
        $wishlistcnt = 0;
        $wishlistid = 0;
        if ($customerId > 0) {
            $wishlist = $this->wishlistRepository->getWishlistItem($customerId, $productData->productid);
            if ($wishlist) {
                $wishlistcnt = 1;
                $wishlistid = $wishlist->wishlistid;
            }
        }
        $productData->wishlistid = $wishlistid;

        // Get parent category
        $parentcategory = '';
        $parentcategorycode = '';
        $categoryArr = null;
        if ($productData->parentid > 0) {
            $categoryArr = Category::where('categoryid', $productData->parentid)
                ->where('ispublished', 1)
                ->first();
            if ($categoryArr) {
                $parentcategory = $locale == 'ar' ? $categoryArr->categoryAR : $categoryArr->category;
                $parentcategorycode = $categoryArr->categorycode;
            }
        }
        
        // Get related products
        $relatedProducts = $this->productRepository->getRelatedProducts($productData, $locale, $customerId);
        
        // Get Delivery & Returns
        $deliveryReturns = '';
        if ($locale == 'ar') {
            $deliveryReturns = $this->settingsArr['Delivery & Returns (AR)'] ?? '';
        } else {
            $deliveryReturns = $this->settingsArr['Delivery & Returns'] ?? '';
        }

        // Get gift messages if enabled
        $messages = collect([]);
        $showGiftMessage = false;
        $giftMessageCharge = 0;
        if ($productData->qty > 0) {
            $showGiftMessageSetting = $this->settingsArr['Show Gift Message'] ?? 'No';
            if ($showGiftMessageSetting == 'Yes') {
                $messages = $this->cartRepository->getMessages();
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

        // Get currency info from parent class
        $currencyCode = $this->currencyCode;
        $currencyRate = $this->currencyRate;
        
        return view('frontend.product.show', compact(
            'locale',
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
            'internationDelivery',
            'currencyCode',
            'currencyRate'
        ));
    }
    
    public function showByCode($locale, $product)
    {
        $productData = $this->productRepository->getProductData($product, $locale);
        
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
}
