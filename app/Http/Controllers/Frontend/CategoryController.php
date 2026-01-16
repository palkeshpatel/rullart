<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class CategoryController extends FrontendController
{
    public function index($locale, $categoryCode)
    {
        // Ensure locale is set correctly from URL segment
        if (!in_array($locale, ['en', 'ar'])) {
            $locale = $this->locale ?? 'en';
        }
        app()->setLocale($locale);
        session(['locale' => $locale]);
        
        $currentDb = config('database.connections.mysql.database');
        $currentPort = request()->getPort();

        \Log::info("CategoryController index called");
        \Log::info("Request URL: " . request()->fullUrl());
        \Log::info("Request Path: " . request()->path());
        \Log::info("Current Port: {$currentPort}");
        \Log::info("Current Database: {$currentDb}");
        \Log::info("Route parameters: " . json_encode(request()->route()->parameters()));

        // Get categoryCode from route parameters directly to ensure correct value
        // Laravel binds route parameters by position, so we need to get it from route
        $categoryCode = request()->route('categoryCode') ?? $categoryCode;
        \Log::info("categoryCode from route: " . request()->route('categoryCode'));
        \Log::info("categoryCode parameter value: {$categoryCode}");

        // Use locale from route parameter
        $locale = $locale ?? app()->getLocale();

        $currentDb = config('database.connections.mysql.database');
        \Log::info("CategoryController: Searching for category '{$categoryCode}' in database: {$currentDb}");

        $category = Category::where('categorycode', $categoryCode)
            ->where('ispublished', 1)
            ->first();

        \Log::info("Category found: " . ($category ? "Yes - ID: {$category->categoryid}, Code: {$category->categorycode}" : "No"));

        if (!$category) {
            \Log::error("CategoryController: Category '{$categoryCode}' not found or not published in database: {$currentDb}");
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

        try {
            \Log::info("Calling getCategoryProducts for: {$categoryCode}");
            $collections = $this->getCategoryProducts($categoryCode, $locale, $sortby, $color, $size, $price, $page, $main, $subcategory);

            \Log::info("getCategoryProducts returned: " . ($collections ? "Data" : "False"));

            if (!$collections) {
                \Log::error("CategoryController: getCategoryProducts returned false for category: {$categoryCode}");
                abort(404, 'Category not found');
            }
        } catch (\Exception $e) {
            \Log::error("CategoryController index error: " . $e->getMessage());
            \Log::error($e->getTraceAsString());
            abort(404, 'Error loading category: ' . $e->getMessage());
        }

        // Prepare meta data
        $metaTitle = $locale == 'ar'
            ? ($this->settingsArr['Website Title'] ?? 'Rullart') . ' : ' . $collections['category']->metatitleAR
            : ($this->settingsArr['Website Title'] ?? 'Rullart') . ' : ' . $collections['category']->metatitle;

        $metaDescription = $locale == 'ar'
            ? $collections['category']->metadescrAR
            : $collections['category']->metadescr;

        // Prepare view data using helper method
        $data = $this->prepareViewData($collections, $locale, $metaTitle, $metaDescription, [
            'page' => $page,
            'main' => $main,
            'sortby' => $sortby,
            'color' => $color,
            'size' => $size,
            'price' => $price,
            'categoryCode' => $categoryCode,
            'isall' => false,
        ]);

        // Check if gift category (categoryid == 80)
        if ($collections['category']->categoryid == 80) {
            return view('frontend.category.gift-category', $data);
        }

        return view('frontend.category.index', $data);
    }

    public function all()
    {
        $locale = app()->getLocale();

        // Get filter parameters
        $sortby = request()->get('sortby', 'relevance');
        $color = request()->get('color', '');
        $size = request()->get('size', '');
        $price = request()->get('price', '');
        $page = request()->get('page', 1);
        $main = request()->get('main', 0);
        $subcategory = request()->get('category', '');

        try {
            $collections = $this->getAllCategoryProducts($locale, $sortby, $color, $size, $price, $page, $main, $subcategory);

            if (!$collections) {
                abort(404);
            }

            $metaTitle = __('All Products');
            $metaDescription = '';

            // Prepare view data using helper method
            $data = $this->prepareViewData($collections, $locale, $metaTitle, $metaDescription, [
                'page' => $page,
                'main' => $main,
                'sortby' => $sortby,
                'color' => $color,
                'size' => $size,
                'price' => $price,
                'categoryCode' => '',
                'isall' => true,
            ]);

            return view('frontend.category.index', $data);
        } catch (\Exception $e) {
            \Log::error('CategoryController all error: ' . $e->getMessage());
            abort(404);
        }
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
        // Get locale from URL segment (most reliable) or session
        $locale = request()->segment(1);
        if (!in_array($locale, ['en', 'ar'])) {
            $locale = session('locale', app()->getLocale() ?: 'en');
        }
        
        // Ensure locale is set in application
        app()->setLocale($locale);
        session(['locale' => $locale]);

        // Get filter parameters
        $sortby = request()->get('sortby', 'relevance');
        $color = request()->get('color', '');
        $size = request()->get('size', '');
        $price = request()->get('price', '');
        $page = request()->get('page', 1);
        $category = request()->get('category', '');

        try {
            $collections = $this->getNewProductsWithFilters($locale, $sortby, $color, $size, $price, $page, $category);

            if (!$collections) {
                abort(404);
            }

            // Create dummy category object for "What's New"
            $whatsNewTranslation = trans('common.Whats New', [], $locale);
            $categoryObj = (object)[
                'categoryid' => 0,
                'categorycode' => 'whatsnew',
                'category' => $whatsNewTranslation,
                'categoryAR' => $whatsNewTranslation,
                'metatitle' => $whatsNewTranslation,
                'metatitleAR' => $whatsNewTranslation,
                'metakeyword' => '',
                'metakeywordAR' => '',
                'metadescr' => '',
                'metadescrAR' => '',
                'photo' => null,
            ];

            $collections['category'] = $categoryObj;

            $metaTitle = $whatsNewTranslation;
            $metaDescription = '';

            // Prepare view data using helper method
            $data = $this->prepareViewData($collections, $locale, $metaTitle, $metaDescription, [
                'page' => $page,
                'main' => 0,
                'sortby' => $sortby,
                'color' => $color,
                'size' => $size,
                'price' => $price,
                'categoryCode' => '',
                'isall' => false,
            ]);

            return view('frontend.category.index', $data);
        } catch (\Exception $e) {
            \Log::error('CategoryController whatsNew error: ' . $e->getMessage());
            abort(404);
        }
    }

    public function sale()
    {
        // Get locale from URL segment (most reliable) or session
        $locale = request()->segment(1);
        if (!in_array($locale, ['en', 'ar'])) {
            $locale = session('locale', app()->getLocale() ?: 'en');
        }
        
        // Ensure locale is set in application
        app()->setLocale($locale);
        session(['locale' => $locale]);

        // Get filter parameters
        $sortby = request()->get('sortby', 'relevance');
        $color = request()->get('color', '');
        $size = request()->get('size', '');
        $price = request()->get('price', '');
        $page = request()->get('page', 1);
        $category = request()->get('category', '');

        try {
            $collections = $this->getSaleProductsWithFilters($locale, $sortby, $color, $size, $price, $page, $category);

            if (!$collections) {
                abort(404);
            }

            // Create dummy category object for "Sale"
            $saleTranslation = trans('common.Sale', [], $locale);
            $categoryObj = (object)[
                'categoryid' => 0,
                'categorycode' => 'sale',
                'category' => $saleTranslation,
                'categoryAR' => $saleTranslation,
                'metatitle' => $saleTranslation,
                'metatitleAR' => $saleTranslation,
                'metakeyword' => '',
                'metakeywordAR' => '',
                'metadescr' => '',
                'metadescrAR' => '',
                'photo' => null,
            ];

            $collections['category'] = $categoryObj;

            $metaTitle = $saleTranslation;
            $metaDescription = '';

            // Prepare view data using helper method
            $data = $this->prepareViewData($collections, $locale, $metaTitle, $metaDescription, [
                'page' => $page,
                'main' => 0,
                'sortby' => $sortby,
                'color' => $color,
                'size' => $size,
                'price' => $price,
                'categoryCode' => '',
                'isall' => false,
            ]);

            return view('frontend.category.index', $data);
        } catch (\Exception $e) {
            \Log::error('CategoryController sale error: ' . $e->getMessage());
            abort(404);
        }
    }

    protected function getCategoryProducts($categoryCode, $locale, $sortby = 'relevance', $color = '', $size = '', $price = '', $page = 1, $main = 0, $subcategory = '')
    {
        try {
            $currencyCode = $this->currencyCode;
            $currencyRate = $this->currencyRate;
            $customerId = session('customerid', 0);
            $perPage = 20;

            // Get category info
            $category = Category::where('categorycode', $categoryCode)
                ->where('ispublished', 1)
                ->first();

            if (!$category) {
                \Log::error("CategoryController: Category '{$categoryCode}' not found or not published in database: " . config('database.connections.mysql.database'));
                return false;
            }

            // Check if productpriceview exists (it's a VIEW, not a TABLE)
            // Note: Both Kuwait and Qatar have this view, but we check anyway
            $currentDb = config('database.connections.mysql.database');
            $hasProductPriceView = false;

            // Method 1: Try to query the view directly (most reliable)
            try {
                DB::selectOne("SELECT 1 FROM productpriceview LIMIT 1");
                $hasProductPriceView = true;
                \Log::info("CategoryController: productpriceview exists (verified by direct query) in database: {$currentDb}");
            } catch (\Exception $e) {
                \Log::warning("CategoryController: Direct query to productpriceview failed: " . $e->getMessage());

                // Method 2: Check information_schema as fallback
                try {
                    $result = DB::selectOne("
                        SELECT 1 as exists_check
                        FROM information_schema.views
                        WHERE table_schema = ?
                          AND table_name = 'productpriceview'
                    ", [$currentDb]);
                    $hasProductPriceView = !empty($result);
                    if ($hasProductPriceView) {
                        \Log::info("CategoryController: productpriceview exists (verified by information_schema) in database: {$currentDb}");
                    }
                } catch (\Exception $e2) {
                    \Log::warning("CategoryController: information_schema check also failed: " . $e2->getMessage());
                }
            }

            \Log::info("productpriceview check result for database {$currentDb}: " . ($hasProductPriceView ? "EXISTS" : "NOT FOUND"));

            // Don't fail if view doesn't exist - just log warning and continue
            // The code will fall back to using products table directly
            if (!$hasProductPriceView) {
                \Log::warning('CategoryController: productpriceview view not found in database: ' . $currentDb);
                \Log::warning('CategoryController: Will attempt to use products table directly');
                // Continue anyway - the code handles this case
            }

            // Build where category clause (handles parent/child relationships)
            $categoryId = $category->categoryid;
            $parentId = $category->parentid;

            // Get subcategories
            $subcategoriesQuery = DB::table('category as c')
                ->select([
                    $locale == 'ar' ? 'c.categoryAR as category' : 'c.category',
                    'c.categoryid',
                    'c.categorycode',
                    'c.parentid',
                    'c.categoryAR',
                    DB::raw("(SELECT COUNT(*) FROM products WHERE ispublished=1 AND fkcategoryid=c.categoryid) as productcnt")
                ])
                ->distinct()
                ->join('products as p', 'p.fkcategoryid', '=', 'c.categoryid')
                ->where('p.ispublished', 1)
                ->where('c.ispublished', 1);

            if ($main == 0) {
                if ($parentId > 0) {
                    $subcategoriesQuery->where(function ($query) use ($parentId, $categoryId) {
                        $query->where('c.parentid', $parentId)
                            ->orWhere('c.categoryid', $parentId);
                    });
                } else {
                    $subcategoriesQuery->where(function ($query) use ($categoryId) {
                        $query->where('c.parentid', $categoryId)
                            ->orWhere('c.categoryid', $categoryId);
                    });
                }
            } else {
                $subcategoriesQuery->where('c.categoryid', $categoryId);
            }

            $subcategoriesQuery->orderBy('c.parentid')
                ->orderBy('c.displayorder');

            $subcategories = $subcategoriesQuery->get();

            // Build products query
            $titleColumn = $locale == 'ar' ? 'p.shortdescrAR as title' : 'p.shortdescr as title';

            $productsQuery = DB::table('products as p')
                ->select([
                    DB::raw($locale == 'ar' ? 'p.shortdescrAR as title' : 'p.shortdescr as title'),
                    'p.productid',
                    'p.productcode',
                    'p.price',
                    'p.photo1',
                    'c.categorycode',
                    DB::raw("IFNULL((SELECT SUM(qty) FROM productsfilter WHERE fkproductid=p.productid AND productsfilter.filtercode='size'), 0) as qty")
                ])
                ->distinct();

            if ($hasProductPriceView) {
                $column = \App\Helpers\TenantHelper::getProductPriceViewColumn();
                $productsQuery->addSelect(['pp.discount', 'pp.sellingprice'])
                    ->join('productpriceview as pp', "pp.{$column}", '=', 'p.productid');
            } else {
                $productsQuery->addSelect([
                    DB::raw('COALESCE(p.discount, 0) as discount'),
                    DB::raw('COALESCE(p.sellingprice, p.price) as sellingprice')
                ]);
            }

            if ($customerId) {
                $productsQuery->addSelect([DB::raw('IFNULL(w.wishlistid, 0) as wishlistid')]);
            } else {
                $productsQuery->addSelect([DB::raw('0 as wishlistid')]);
            }

            $productsQuery->leftJoin('category as c', 'c.categoryid', '=', 'p.fkcategoryid')
                ->where('p.ispublished', 1)
                ->where('c.ispublished', 1);

            // Apply subcategory filter if provided
            if ($subcategory) {
                $productsQuery->where('c.categorycode', $subcategory);
            } else {
                if ($main == 0) {
                    if ($parentId > 0) {
                        $productsQuery->where(function ($query) use ($parentId, $categoryId) {
                            $query->where('c.parentid', $parentId)
                                ->orWhere('c.categoryid', $parentId);
                        });
                    } else {
                        $productsQuery->where(function ($query) use ($categoryId) {
                            $query->where('c.parentid', $categoryId)
                                ->orWhere('c.categoryid', $categoryId);
                        });
                    }
                } else {
                    $productsQuery->where('c.categoryid', $categoryId);
                }
            }

            // Add wishlist join if customer is logged in
            if ($customerId) {
                $productsQuery->leftJoin('wishlist as w', function ($join) use ($customerId) {
                    $join->on('p.productid', '=', 'w.fkproductid')
                        ->where('w.fkcustomerid', '=', $customerId);
                });
            }

            // Apply color filter
            if ($color) {
                $productsQuery->join('productsfilter as pfcolor', function ($join) {
                    $join->on('p.productid', '=', 'pfcolor.fkproductid')
                        ->where('pfcolor.filtercode', '=', 'color');
                });
                // Get color IDs from filtervaluecode (handle comma-separated values)
                $colorCodes = is_array($color) ? $color : explode(',', $color);
                $colorCodes = array_filter(array_map('trim', $colorCodes)); // Remove empty values

                if (!empty($colorCodes)) {
                    $colorIds = DB::table('filtervalues')
                        ->whereIn('filtervaluecode', $colorCodes)
                        ->where('fkfilterid', 2)
                        ->pluck('filtervalueid')
                        ->toArray();
                    if (!empty($colorIds)) {
                        $productsQuery->whereIn('pfcolor.fkfiltervalueid', $colorIds);
                    } else {
                        // If no color IDs found, return no products
                        $productsQuery->whereRaw('1 = 0');
                    }
                }
            }

            // Apply size filter
            if ($size) {
                $productsQuery->join('productsfilter as pfsize', function ($join) {
                    $join->on('p.productid', '=', 'pfsize.fkproductid')
                        ->where('pfsize.filtercode', '=', 'size');
                });
                // Get size IDs from filtervaluecode (handle comma-separated values)
                $sizeCodes = is_array($size) ? $size : explode(',', $size);
                $sizeCodes = array_filter(array_map('trim', $sizeCodes)); // Remove empty values

                if (!empty($sizeCodes)) {
                    $sizeIds = DB::table('filtervalues')
                        ->whereIn('filtervaluecode', $sizeCodes)
                        ->where('fkfilterid', 3)
                        ->pluck('filtervalueid')
                        ->toArray();
                    if (!empty($sizeIds)) {
                        $productsQuery->whereIn('pfsize.fkfiltervalueid', $sizeIds)
                            ->where('pfsize.qty', '>', 0);
                    } else {
                        // If no size IDs found, return no products
                        $productsQuery->whereRaw('1 = 0');
                    }
                }
            }

            // Apply price filter
            // Note: Price filter values are in BASE currency (not converted), matching CI behavior
            if ($price) {
                $prices = explode('-', $price);
                $sellingPriceColumn = $hasProductPriceView ? 'pp.sellingprice' : DB::raw('COALESCE(p.sellingprice, p.price)');
                if (count($prices) == 1) {
                    // Single price value (already in base currency)
                    $priceValue = $prices[0];
                    if ($price == 5) {
                        $productsQuery->where($sellingPriceColumn, '<=', $priceValue);
                    } else {
                        $productsQuery->where($sellingPriceColumn, '>', $priceValue);
                    }
                } else if (count($prices) == 2) {
                    // Price range (already in base currency)
                    $minPrice = $prices[0];
                    $maxPrice = $prices[1];
                    $productsQuery->where($sellingPriceColumn, '>=', $minPrice)
                        ->where($sellingPriceColumn, '<', $maxPrice);
                }
            }

            // Apply sorting
            if ($sortby == 'lowtohigh') {
                $sortColumn = $hasProductPriceView ? 'pp.sellingprice' : DB::raw('COALESCE(p.sellingprice, p.price)');
                $productsQuery->orderBy($sortColumn, 'asc');
            } else if ($sortby == 'hightolow') {
                $sortColumn = $hasProductPriceView ? 'pp.sellingprice' : DB::raw('COALESCE(p.sellingprice, p.price)');
                $productsQuery->orderBy($sortColumn, 'desc');
            } else {
                $productsQuery->orderBy($locale == 'ar' ? 'p.shortdescrAR' : 'p.shortdescr', 'asc');
            }

            // Get total count
            try {
                \Log::info("Executing products count query");
                $productcnt = $productsQuery->count();
                \Log::info("Product count: {$productcnt}");
                $totalpage = ceil($productcnt / $perPage);

                // Get products with pagination
                if ($page == 1) {
                    \Log::info("Getting products for page 1");
                    $products = $productsQuery->limit($perPage * $page)->get();
                } else {
                    $offset = ($page - 1) * $perPage;
                    \Log::info("Getting products for page {$page}, offset {$offset}");
                    $products = $productsQuery->offset($offset)->limit($perPage)->get();
                }
                \Log::info("Products retrieved: " . $products->count());

                // Calculate discount percentage for each product (if discount is stored as amount)
                $products = $products->map(function ($product) {
                    if (isset($product->discount) && isset($product->price) && $product->price > 0) {
                        // If discount is stored as amount, calculate percentage
                        if ($product->discount > 0 && $product->discount < $product->price) {
                            $product->discount = round(($product->discount / $product->price) * 100, 2);
                        }
                    }
                    return $product;
                });
            } catch (\Exception $e) {
                \Log::error("Error in products query: " . $e->getMessage());
                \Log::error("Query: " . $productsQuery->toSql());
                throw $e;
            }

            // Get price range (using the same query conditions as products, including subcategory filter)
            $priceQuery = clone $productsQuery;
            if ($hasProductPriceView) {
                $productPrices = $priceQuery->select('pp.sellingprice')->get();
            } else {
                $productPrices = $priceQuery->select(DB::raw('COALESCE(p.sellingprice, p.price) as sellingprice'))->get();
            }
            $prices1 = $productPrices->pluck('sellingprice')->toArray();
            $pricerange = $this->createPriceRange($prices1, $currencyCode, $currencyRate);

            // Get colors array
            $colorsQuery = DB::table('productsfilter as pf')
                ->select([
                    $locale == 'ar' ? 'fv.filtervalueAR as filtervalue' : 'fv.filtervalue',
                    'pf.filtercode',
                    'fv.filtervaluecode',
                    'fv.displayorder',
                    DB::raw('COUNT(p.productid) as cnt')
                ])
                ->distinct()
                ->join('products as p', 'pf.fkproductid', '=', 'p.productid')
                ->join('category as c', 'c.categoryid', '=', 'p.fkcategoryid')
                ->join('filtervalues as fv', 'fv.filtervalueid', '=', 'pf.fkfiltervalueid')
                ->where('p.ispublished', 1)
                ->where('c.ispublished', 1)
                ->where('fv.isactive', 1)
                ->where('fv.fkfilterid', 2)
                ->where('pf.filtercode', 'color');

            // Apply subcategory filter if provided
            if ($subcategory) {
                $colorsQuery->where('c.categorycode', $subcategory);
            } else {
                if ($main == 0) {
                    if ($parentId > 0) {
                        $colorsQuery->where(function ($query) use ($parentId, $categoryId) {
                            $query->where('c.parentid', $parentId)
                                ->orWhere('c.categoryid', $parentId);
                        });
                    } else {
                        $colorsQuery->where(function ($query) use ($categoryId) {
                            $query->where('c.parentid', $categoryId)
                                ->orWhere('c.categoryid', $categoryId);
                        });
                    }
                } else {
                    $colorsQuery->where('c.categoryid', $categoryId);
                }
            }

            if ($size) {
                $colorsQuery->join('productsfilter as pfsize', function ($join) use ($sizeIds) {
                    $join->on('p.productid', '=', 'pfsize.fkproductid')
                        ->where('pfsize.filtercode', '=', 'size');
                    if (!empty($sizeIds)) {
                        $join->whereIn('pfsize.fkfiltervalueid', $sizeIds);
                    }
                });
            }

            if ($price) {
                $prices = explode('-', $price);
                if ($hasProductPriceView) {
                    $column = \App\Helpers\TenantHelper::getProductPriceViewColumn();
                    $colorsQuery->join('productpriceview as pp', "pp.{$column}", '=', 'p.productid');
                    $sellingPriceColumn = 'pp.sellingprice';
                } else {
                    $sellingPriceColumn = DB::raw('COALESCE(p.sellingprice, p.price)');
                }
                // Price filter values are in BASE currency (not converted)
                if (count($prices) == 1) {
                    $priceValue = $prices[0];
                    if ($price == 5) {
                        $colorsQuery->where($sellingPriceColumn, '<=', $priceValue);
                    } else {
                        $colorsQuery->where($sellingPriceColumn, '>', $priceValue);
                    }
                } else if (count($prices) == 2) {
                    $minPrice = $prices[0];
                    $maxPrice = $prices[1];
                    $colorsQuery->where($sellingPriceColumn, '>=', $minPrice)
                        ->where($sellingPriceColumn, '<=', $maxPrice);
                }
            }

            $colorsArr = $colorsQuery->groupBy([
                'fv.filtervaluecode',
                $locale == 'ar' ? 'fv.filtervalueAR' : 'fv.filtervalue',
                'pf.filtercode',
                'fv.displayorder'
            ])
                ->orderBy('pf.filtercode')
                ->orderBy($locale == 'ar' ? 'fv.filtervalueAR' : 'fv.filtervalue')
                ->get();

            // Get sizes array
            $sizesQuery = DB::table('productsfilter as pf')
                ->select([
                    $locale == 'ar' ? 'fv.filtervalueAR as filtervalue' : 'fv.filtervalue',
                    'pf.filtercode',
                    'fv.filtervaluecode',
                    'fv.displayorder',
                    DB::raw('COUNT(p.productid) as cnt')
                ])
                ->distinct()
                ->join('products as p', 'pf.fkproductid', '=', 'p.productid')
                ->join('category as c', 'c.categoryid', '=', 'p.fkcategoryid')
                ->join('filtervalues as fv', 'fv.filtervalueid', '=', 'pf.fkfiltervalueid')
                ->where('p.ispublished', 1)
                ->where('c.ispublished', 1)
                ->where('fv.isactive', 1)
                ->where('fv.fkfilterid', 3)
                ->where('pf.filtercode', 'size')
                ->where('fv.filtervalueid', '>', 0);

            // Apply subcategory filter if provided
            if ($subcategory) {
                $sizesQuery->where('c.categorycode', $subcategory);
            } else {
                if ($main == 0) {
                    if ($parentId > 0) {
                        $sizesQuery->where(function ($query) use ($parentId, $categoryId) {
                            $query->where('c.parentid', $parentId)
                                ->orWhere('c.categoryid', $parentId);
                        });
                    } else {
                        $sizesQuery->where(function ($query) use ($categoryId) {
                            $query->where('c.parentid', $categoryId)
                                ->orWhere('c.categoryid', $categoryId);
                        });
                    }
                } else {
                    $sizesQuery->where('c.categoryid', $categoryId);
                }
            }

            if ($color) {
                $sizesQuery->join('productsfilter as pfcolor', function ($join) use ($colorIds) {
                    $join->on('p.productid', '=', 'pfcolor.fkproductid')
                        ->where('pfcolor.filtercode', '=', 'color');
                    if (!empty($colorIds)) {
                        $join->whereIn('pfcolor.fkfiltervalueid', $colorIds);
                    }
                });
            }

            if ($price) {
                $prices = explode('-', $price);
                if ($hasProductPriceView) {
                    $column = \App\Helpers\TenantHelper::getProductPriceViewColumn();
                    $sizesQuery->join('productpriceview as pp', "pp.{$column}", '=', 'p.productid');
                    $sellingPriceColumn = 'pp.sellingprice';
                } else {
                    $sellingPriceColumn = DB::raw('COALESCE(p.sellingprice, p.price)');
                }
                // Price filter values are in BASE currency (not converted)
                if (count($prices) == 1) {
                    $priceValue = $prices[0];
                    if ($price == 5) {
                        $sizesQuery->where($sellingPriceColumn, '<=', $priceValue);
                    } else {
                        $sizesQuery->where($sellingPriceColumn, '>', $priceValue);
                    }
                } else if (count($prices) == 2) {
                    $minPrice = $prices[0];
                    $maxPrice = $prices[1];
                    $sizesQuery->where($sellingPriceColumn, '>=', $minPrice)
                        ->where($sellingPriceColumn, '<=', $maxPrice);
                }
            }

            $sizesArr = $sizesQuery->groupBy([
                'fv.filtervaluecode',
                $locale == 'ar' ? 'fv.filtervalueAR' : 'fv.filtervalue',
                'pf.filtercode',
                'fv.displayorder'
            ])
                ->orderBy('fv.displayorder')
                ->get();

            return [
                'category' => $category,
                'products' => $products,
                'subcategory' => $subcategories,
                'productcnt' => $productcnt,
                'totalpage' => $totalpage,
                'colorsArr' => $colorsArr,
                'sizesArr' => $sizesArr,
                'pricerange' => $pricerange,
            ];
        } catch (\Exception $e) {
            \Log::error('CategoryController getCategoryProducts error: ' . $e->getMessage());
            \Log::error('Database: ' . config('database.connections.mysql.database'));
            \Log::error('Category Code: ' . $categoryCode);
            \Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    protected function createPriceRange($prices, $currencyCode, $currencyRate)
    {
        if (empty($prices)) {
            return [];
        }

        // Sort prices (matching CI)
        sort($prices);

        // Range limits matching CI's createRange function exactly
        $rangeLimits = [0, 1, 5, 10, 15, 20, 30, 40, 50, 60, 70, 80, 90, 100, 125, 150, 175, 200, 225, 250, 275, 300, 325, 350, 375, 400, 425, 450, 475, 500, 600, 700, 800, 900, 1000, 1200, 1500, 2000, 2500, 3000, 3500, 4000];

        $ranges = [];
        $rangevalue = [];

        for ($i = 0; $i < count($rangeLimits); $i++) {
            $price = $rangeLimits[$i];
            if ($i == 0) {
                $lowLimit = $rangeLimits[$i];
                foreach ($prices as $perPrice) {
                    if ($perPrice < $price) {
                        $convertedPrice = number_format($price * $currencyRate, 0);
                        $text = __('Below') . ' ' . $currencyCode . ' ' . $convertedPrice;
                        $slab = [
                            'price' => '0-' . $price,
                            'cnt' => 1,
                            'text' => $text
                        ];
                        $ranges[] = $slab;
                        if (!(in_array(0, $rangevalue))) {
                            array_push($rangevalue, 0);
                            break;
                        } else {
                            $pos = array_search($lowLimit, $rangevalue);
                            $slab = $ranges[$pos];
                            $slab['cnt'] = $slab['cnt'] + 1;
                            $ranges[$pos] = $slab;
                        }
                    }
                }
            } else if ($i == count($rangeLimits) - 1) {
                $lowLimit = $rangeLimits[$i];
                foreach ($prices as $perPrice) {
                    if ($perPrice > $price) {
                        $convertedPrice = number_format($price * $currencyRate, 0);
                        $text = __('Above') . ' ' . $currencyCode . ' ' . $convertedPrice;
                        $slab = [
                            'price' => (string) $price,
                            'cnt' => 1,
                            'text' => $text
                        ];
                        $ranges[] = $slab;
                        if (!(in_array($price, $rangevalue))) {
                            array_push($rangevalue, $price);
                            break;
                        } else {
                            $pos = array_search($lowLimit, $rangevalue);
                            $slab = $ranges[$pos];
                            $slab['cnt'] = $slab['cnt'] + 1;
                            $ranges[$pos] = $slab;
                        }
                    }
                }
            } else {
                // Matching CI exactly: $lowLimit = $rangeLimits[$i], $highLimit = $rangeLimits[$i + 1]
                $lowLimit = $rangeLimits[$i];
                $highLimit = $rangeLimits[$i + 1];
                foreach ($prices as $perPrice) {
                    if ($perPrice >= $lowLimit && $perPrice < $highLimit) {
                        $convertedLow = number_format($lowLimit * $currencyRate, 0);
                        $convertedHigh = number_format($highLimit * $currencyRate, 0);
                        $text = $currencyCode . ' ' . $convertedLow . ' to ' . $currencyCode . ' ' . $convertedHigh;
                        $slab = [
                            'price' => $lowLimit . '-' . $highLimit,
                            'cnt' => 1,
                            'text' => $text
                        ];
                        if (!(in_array($lowLimit, $rangevalue))) {
                            array_push($rangevalue, $lowLimit);
                            $ranges[] = $slab;
                        } else {
                            $pos = array_search($lowLimit, $rangevalue);
                            $slab = $ranges[$pos];
                            $slab['cnt'] = $slab['cnt'] + 1;
                            $ranges[$pos] = $slab;
                        }
                    }
                }
            }
        }

        // Convert cnt to string (matching CI line 260)
        foreach ($ranges as $key => $value) {
            $value['cnt'] = strval($value['cnt']);
            $ranges[$key] = $value;
        }

        // Convert to objects for consistency
        return array_map(function ($item) {
            return (object) $item;
        }, $ranges);
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
            $column = \App\Helpers\TenantHelper::getProductPriceViewColumn();
            $query->leftJoin('productpriceview as pp', "pp.{$column}", '=', 'p.productid')
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
            $column = \App\Helpers\TenantHelper::getProductPriceViewColumn();
            $query->leftJoin('productpriceview as pp', "pp.{$column}", '=', 'p.productid')
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
            $column = \App\Helpers\TenantHelper::getProductPriceViewColumn();
            $query->leftJoin('productpriceview as pp', "pp.{$column}", '=', 'p.productid')
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

    /**
     * AJAX endpoint for product listing (matches CI Prodlisting->category)
     * Returns JSON for dynamic filtering
     */
    public function prodlisting($locale, $categoryCode)
    {
        $locale = $locale ?? app()->getLocale();

        // Handle 'all' category
        if ($categoryCode == 'all') {
            $categoryCode = '';
        }

        // Get filter parameters
        $sortby = request()->get('sortby', 'relevance');
        $color = request()->get('color', '');
        $size = request()->get('size', '');
        $price = request()->get('price', '');
        $page = request()->get('page', 1);
        $main = request()->get('main', 0);
        $subcategory = request()->get('category', '');
        $firstload = request()->get('firstload', 1);

        \Log::info("CategoryController prodlisting called");
        \Log::info("Request URL: " . request()->fullUrl());
        \Log::info("Category Code: {$categoryCode}");
        \Log::info("Color filter: {$color}");
        \Log::info("Size filter: {$size}");
        \Log::info("Price filter: {$price}");
        \Log::info("Sort by: {$sortby}");

        try {
            if ($categoryCode == '') {
                // Get all products
                $collections = $this->getAllCategoryProducts($locale, $sortby, $color, $size, $price, $page, $main, $subcategory);
            } else {
                // Get category products
                $collections = $this->getCategoryProducts($categoryCode, $locale, $sortby, $color, $size, $price, $page, $main, $subcategory);
            }

            \Log::info("prodlisting collections: " . ($collections ? "Found " . count($collections['products']) . " products" : "False"));

            if (!$collections || count($collections['products']) == 0) {
                return response()->json('FALSE');
            }

            // Handle 'all' category metadata
            if ($categoryCode == '') {
                $collections['category']->category = 'All';
                $collections['category']->categoryAR = 'All';
                $collections['category']->metatitleAR = 'All';
                $collections['category']->metatitle = 'All';
                $collections['category']->metakeyword = 'All';
                $collections['category']->metakeywordAR = 'All';
                $collections['category']->metadescr = 'All';
                $collections['category']->metadescrAR = 'All';
                $collections['category']->categorycode = 'all';
            }

            // Format products for JSON response (ensure discount is percentage)
            $formattedProducts = $collections['products']->map(function ($product) {
                // Calculate discount percentage if needed
                if (isset($product->discount) && isset($product->price) && $product->price > 0) {
                    // If discount appears to be an amount (less than price), convert to percentage
                    if ($product->discount > 0 && $product->discount < $product->price) {
                        $product->discount = round(($product->discount / $product->price) * 100, 2);
                    }
                }
                return $product;
            });

            // Prepare filter data for sidefilter view
            $filterData = $this->prepareFilterData($collections, $locale, $subcategory ?: $categoryCode, $color, $size, $price);

            // Get side filter HTML
            $sideFilterHtml = view('frontend.category.sidefilter', $filterData)->render();

            // Clean up HTML (remove newlines, tabs, etc. - matching CI)
            $sideFilterHtml = str_replace(["\r", "\n", "\t"], '', $sideFilterHtml);

            // Return JSON matching CI format
            return response()->json([
                'products' => $formattedProducts->values(),
                'subcategory' => $collections['subcategory'],
                'productcnt' => $collections['productcnt'],
                'totalpage' => $collections['totalpage'],
                'sidefilter' => $sideFilterHtml,
            ]);
        } catch (\Exception $e) {
            \Log::error('CategoryController prodlisting error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            return response()->json('FALSE');
        }
    }

    /**
     * AJAX endpoint for occasion product listing
     */
    public function prodlistingOccassion($locale, $occassion)
    {
        $locale = $locale ?? app()->getLocale();

        $sortby = request()->get('sortby', 'relevance');
        $color = request()->get('color', '');
        $size = request()->get('size', '');
        $price = request()->get('price', '');
        $page = request()->get('page', 1);
        $main = request()->get('main', 0);
        $category = request()->get('category', '');
        $firstload = request()->get('firstload', 1);

        try {
            $collections = $this->getOccassionProducts($occassion, $locale, $sortby, $color, $size, $price, $page, $main, $category);

            if (!$collections || count($collections['products']) == 0) {
                return response()->json('FALSE');
            }

            // Prepare filter data for sidefilter view
            $filterData = $this->prepareFilterData($collections, $locale, $category, $color, $size, $price);

            $sideFilterHtml = view('frontend.category.sidefilter', $filterData)->render();
            $sideFilterHtml = str_replace(["\r", "\n", "\t"], '', $sideFilterHtml);

            return response()->json([
                'products' => $collections['products'],
                'subcategory' => $collections['subcategory'],
                'productcnt' => $collections['productcnt'],
                'totalpage' => $collections['totalpage'],
                'sidefilter' => $sideFilterHtml,
            ]);
        } catch (\Exception $e) {
            \Log::error('CategoryController prodlistingOccassion error: ' . $e->getMessage());
            return response()->json('FALSE');
        }
    }

    /**
     * AJAX endpoint for what's new product listing
     */
    public function prodlistingWhatsnew($locale)
    {
        $locale = $locale ?? app()->getLocale();

        $sortby = request()->get('sortby', 'relevance');
        $color = request()->get('color', '');
        $size = request()->get('size', '');
        $price = request()->get('price', '');
        $page = request()->get('page', 1);
        $category = request()->get('category', '');
        $firstload = request()->get('firstload', 1);

        try {
            $collections = $this->getNewProductsWithFilters($locale, $sortby, $color, $size, $price, $page, $category);

            if (!$collections || count($collections['products']) == 0) {
                return response()->json('FALSE');
            }

            // Prepare filter data for sidefilter view
            $filterData = $this->prepareFilterData($collections, $locale, $category, $color, $size, $price);

            $sideFilterHtml = view('frontend.category.sidefilter', $filterData)->render();
            $sideFilterHtml = str_replace(["\r", "\n", "\t"], '', $sideFilterHtml);

            return response()->json([
                'products' => $collections['products'],
                'subcategory' => $collections['subcategory'],
                'productcnt' => $collections['productcnt'],
                'totalpage' => $collections['totalpage'],
                'sidefilter' => $sideFilterHtml,
            ]);
        } catch (\Exception $e) {
            \Log::error('CategoryController prodlistingWhatsnew error: ' . $e->getMessage());
            return response()->json('FALSE');
        }
    }

    /**
     * AJAX endpoint for sale product listing
     */
    public function prodlistingSale($locale)
    {
        $locale = $locale ?? app()->getLocale();

        $sortby = request()->get('sortby', 'relevance');
        $color = request()->get('color', '');
        $size = request()->get('size', '');
        $price = request()->get('price', '');
        $page = request()->get('page', 1);
        $category = request()->get('category', '');
        $firstload = request()->get('firstload', 1);

        try {
            $collections = $this->getSaleProductsWithFilters($locale, $sortby, $color, $size, $price, $page, $category);

            if (!$collections || count($collections['products']) == 0) {
                return response()->json('FALSE');
            }

            // Prepare filter data for sidefilter view
            $filterData = $this->prepareFilterData($collections, $locale, $category, $color, $size, $price);

            $sideFilterHtml = view('frontend.category.sidefilter', $filterData)->render();
            $sideFilterHtml = str_replace(["\r", "\n", "\t"], '', $sideFilterHtml);

            return response()->json([
                'products' => $collections['products'],
                'subcategory' => $collections['subcategory'],
                'productcnt' => $collections['productcnt'],
                'totalpage' => $collections['totalpage'],
                'sidefilter' => $sideFilterHtml,
            ]);
        } catch (\Exception $e) {
            \Log::error('CategoryController prodlistingSale error: ' . $e->getMessage());
            return response()->json('FALSE');
        }
    }

    /**
     * Get all category products with filters (for 'all' page)
     */
    protected function getAllCategoryProducts($locale, $sortby, $color, $size, $price, $page, $main, $subcategory)
    {
        // This should use similar logic to getCategoryProducts but for all products
        // For now, we'll use a simplified version
        $currencyCode = $this->currencyCode;
        $currencyRate = $this->currencyRate;
        $customerId = session('customerid', 0);
        $perPage = 20;

        // Build products query
        $productsQuery = DB::table('products as p')
            ->select([
                DB::raw($locale == 'ar' ? 'p.shortdescrAR as title' : 'p.shortdescr as title'),
                'p.productid',
                'p.productcode',
                'p.price',
                'p.photo1',
                'c.categorycode',
                DB::raw("IFNULL((SELECT SUM(qty) FROM productsfilter WHERE fkproductid=p.productid AND productsfilter.filtercode='size'), 0) as qty")
            ])
            ->distinct();

        // Add price view join if exists
        $hasProductPriceView = DB::selectOne("
            SELECT 1 FROM information_schema.views
            WHERE table_schema = DATABASE() AND table_name = 'productpriceview'
        ");
        $hasProductPriceView = !empty($hasProductPriceView);

        if ($hasProductPriceView) {
            $column = \App\Helpers\TenantHelper::getProductPriceViewColumn();
            $productsQuery->addSelect(['pp.discount', 'pp.sellingprice'])
                ->join('productpriceview as pp', "pp.{$column}", '=', 'p.productid');
        } else {
            $productsQuery->addSelect([
                DB::raw('COALESCE(p.discount, 0) as discount'),
                DB::raw('COALESCE(p.sellingprice, p.price) as sellingprice')
            ]);
        }

        if ($customerId) {
            $productsQuery->addSelect([DB::raw('IFNULL(w.wishlistid, 0) as wishlistid')]);
        } else {
            $productsQuery->addSelect([DB::raw('0 as wishlistid')]);
        }

        $productsQuery->leftJoin('category as c', 'c.categoryid', '=', 'p.fkcategoryid')
            ->where('p.ispublished', 1)
            ->where('c.ispublished', 1);

        // Apply filters (color, size, price) - similar to getCategoryProducts
        // ... (filter logic here)

        // Apply sorting
        if ($sortby == 'lowtohigh') {
            $sortColumn = $hasProductPriceView ? 'pp.sellingprice' : DB::raw('COALESCE(p.sellingprice, p.price)');
            $productsQuery->orderBy($sortColumn, 'asc');
        } else if ($sortby == 'hightolow') {
            $sortColumn = $hasProductPriceView ? 'pp.sellingprice' : DB::raw('COALESCE(p.sellingprice, p.price)');
            $productsQuery->orderBy($sortColumn, 'desc');
        } else {
            $productsQuery->orderBy($locale == 'ar' ? 'p.shortdescrAR' : 'p.shortdescr', 'asc');
        }

        $productcnt = $productsQuery->count();
        $totalpage = ceil($productcnt / $perPage);

        if ($page == 1) {
            $products = $productsQuery->limit($perPage * $page)->get();
        } else {
            $offset = ($page - 1) * $perPage;
            $products = $productsQuery->offset($offset)->limit($perPage)->get();
        }

        // Get subcategories (all categories with products)
        $subcategories = DB::table('category as c')
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

        // Create dummy category object
        $category = (object)[
            'categoryid' => 0,
            'categorycode' => 'all',
            'category' => 'All',
            'categoryAR' => 'All',
            'metatitle' => 'All',
            'metatitleAR' => 'All',
            'metakeyword' => 'All',
            'metakeywordAR' => 'All',
            'metadescr' => 'All',
            'metadescrAR' => 'All',
        ];

        return [
            'category' => $category,
            'products' => $products,
            'subcategory' => $subcategories,
            'productcnt' => $productcnt,
            'totalpage' => $totalpage,
            'colorsArr' => [],
            'sizesArr' => [],
            'pricerange' => [],
        ];
    }

    /**
     * Get occasion products with filters
     */
    protected function getOccassionProducts($occassion, $locale, $sortby, $color, $size, $price, $page, $main, $category)
    {
        // Similar to getCategoryProducts but filtered by occasion
        // Implementation needed based on CI logic
        return $this->getCategoryProducts($category ?: '', $locale, $sortby, $color, $size, $price, $page, $main, '');
    }

    /**
     * Get new products with filters
     */
    protected function getNewProductsWithFilters($locale, $sortby, $color, $size, $price, $page, $category)
    {
        try {
            $currencyCode = $this->currencyCode;
            $currencyRate = $this->currencyRate;
            $customerId = session('customerid', 0);
            $perPage = 20;

            // Check if productpriceview exists
            try {
                $hasProductPriceView = DB::selectOne("
                    SELECT 1 FROM information_schema.views
                    WHERE table_schema = DATABASE() AND table_name = 'productpriceview'
                ");
                $hasProductPriceView = !empty($hasProductPriceView);
            } catch (\Exception $e) {
                $hasProductPriceView = false;
            }

            // Build products query - filter by isnew = 1
            $productsQuery = DB::table('products as p')
                ->select([
                    DB::raw($locale == 'ar' ? 'p.shortdescrAR as title' : 'p.shortdescr as title'),
                    'p.productid',
                    'p.productcode',
                    'p.price',
                    'p.photo1',
                    'c.categorycode',
                    DB::raw("IFNULL((SELECT SUM(qty) FROM productsfilter WHERE fkproductid=p.productid AND productsfilter.filtercode='size'), 0) as qty")
                ])
                ->distinct()
                ->where('p.ispublished', 1)
                ->where('p.isnew', 1); // Filter for new products

            if ($hasProductPriceView) {
                $column = \App\Helpers\TenantHelper::getProductPriceViewColumn();
                $productsQuery->addSelect(['pp.discount', 'pp.sellingprice'])
                    ->join('productpriceview as pp', "pp.{$column}", '=', 'p.productid');
            } else {
                $productsQuery->addSelect([
                    DB::raw('COALESCE(p.discount, 0) as discount'),
                    DB::raw('COALESCE(p.sellingprice, p.price) as sellingprice')
                ]);
            }

            if ($customerId) {
                $productsQuery->addSelect([DB::raw('IFNULL(w.wishlistid, 0) as wishlistid')]);
            } else {
                $productsQuery->addSelect([DB::raw('0 as wishlistid')]);
            }

            $productsQuery->leftJoin('category as c', 'c.categoryid', '=', 'p.fkcategoryid')
                ->where('c.ispublished', 1);

            // Filter by category if specified
            if ($category) {
                $productsQuery->where('c.categorycode', $category);
            }

            // Add wishlist join if customer is logged in
            if ($customerId) {
                $productsQuery->leftJoin('wishlist as w', function ($join) use ($customerId) {
                    $join->on('p.productid', '=', 'w.fkproductid')
                        ->where('w.fkcustomerid', '=', $customerId);
                });
            }

            // Apply color filter
            $colorIds = [];
            if ($color) {
                $productsQuery->join('productsfilter as pfcolor', function ($join) {
                    $join->on('p.productid', '=', 'pfcolor.fkproductid')
                        ->where('pfcolor.filtercode', '=', 'color');
                });
                $colorIds = DB::table('filtervalues')
                    ->where('filtervaluecode', $color)
                    ->where('fkfilterid', 2)
                    ->pluck('filtervalueid')
                    ->toArray();
                if (!empty($colorIds)) {
                    $productsQuery->whereIn('pfcolor.fkfiltervalueid', $colorIds);
                }
            }

            // Apply size filter
            $sizeIds = [];
            if ($size) {
                $productsQuery->join('productsfilter as pfsize', function ($join) {
                    $join->on('p.productid', '=', 'pfsize.fkproductid')
                        ->where('pfsize.filtercode', '=', 'size');
                });
                $sizeIds = DB::table('filtervalues')
                    ->where('filtervaluecode', $size)
                    ->where('fkfilterid', 3)
                    ->pluck('filtervalueid')
                    ->toArray();
                if (!empty($sizeIds)) {
                    $productsQuery->whereIn('pfsize.fkfiltervalueid', $sizeIds)
                        ->where('pfsize.qty', '>', 0);
                }
            }

            // Apply price filter
            // Note: Price filter values are in BASE currency (not converted), matching CI behavior
            $sellingPriceColumn = $hasProductPriceView ? 'pp.sellingprice' : DB::raw('COALESCE(p.sellingprice, p.price)');
            if ($price) {
                $prices = explode('-', $price);
                if (count($prices) == 1) {
                    $priceValue = $prices[0];
                    if ($price == 5) {
                        $productsQuery->where($sellingPriceColumn, '<=', $priceValue);
                    } else {
                        $productsQuery->where($sellingPriceColumn, '>', $priceValue);
                    }
                } else if (count($prices) == 2) {
                    $minPrice = $prices[0];
                    $maxPrice = $prices[1];
                    $productsQuery->where($sellingPriceColumn, '>=', $minPrice)
                        ->where($sellingPriceColumn, '<', $maxPrice);
                }
            }

            // Apply sorting
            if ($sortby == 'lowtohigh') {
                $sortColumn = $hasProductPriceView ? 'pp.sellingprice' : DB::raw('COALESCE(p.sellingprice, p.price)');
                $productsQuery->orderBy($sortColumn, 'asc');
            } else if ($sortby == 'hightolow') {
                $sortColumn = $hasProductPriceView ? 'pp.sellingprice' : DB::raw('COALESCE(p.sellingprice, p.price)');
                $productsQuery->orderBy($sortColumn, 'desc');
            } else {
                $productsQuery->orderBy($locale == 'ar' ? 'p.shortdescrAR' : 'p.shortdescr', 'asc')
                    ->orderBy('p.productid', 'desc');
            }

            // Get total count
            $productcnt = $productsQuery->count();
            $totalpage = ceil($productcnt / $perPage);

            // Get products with pagination
            if ($page == 1) {
                $products = $productsQuery->limit($perPage * $page)->get();
            } else {
                $offset = ($page - 1) * $perPage;
                $products = $productsQuery->offset($offset)->limit($perPage)->get();
            }

            // Get subcategories (all categories with new products)
            $subcategories = DB::table('category as c')
                ->select([
                    $locale == 'ar' ? 'c.categoryAR as category' : 'c.category',
                    'c.categoryid',
                    'c.categorycode',
                    'c.parentid',
                    DB::raw("(SELECT COUNT(*) FROM products WHERE ispublished=1 AND isnew=1 AND fkcategoryid=c.categoryid) as productcnt")
                ])
                ->distinct()
                ->join('products as p', 'p.fkcategoryid', '=', 'c.categoryid')
                ->where('p.ispublished', 1)
                ->where('p.isnew', 1)
                ->where('c.ispublished', 1)
                ->orderBy('c.parentid')
                ->orderBy('c.displayorder')
                ->get();

            // Get price range, colors, sizes (simplified - can be enhanced later)
            $pricerange = [];
            $colorsArr = [];
            $sizesArr = [];

            return [
                'category' => null,
                'products' => $products,
                'subcategory' => $subcategories,
                'productcnt' => $productcnt,
                'totalpage' => $totalpage,
                'colorsArr' => $colorsArr,
                'sizesArr' => $sizesArr,
                'pricerange' => $pricerange,
            ];
        } catch (\Exception $e) {
            \Log::error('CategoryController getNewProductsWithFilters error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get sale products with filters
     */
    protected function getSaleProductsWithFilters($locale, $sortby, $color, $size, $price, $page, $category)
    {
        try {
            $currencyCode = $this->currencyCode;
            $currencyRate = $this->currencyRate;
            $customerId = session('customerid', 0);
            $perPage = 20;

            // Check if productpriceview exists
            try {
                $hasProductPriceView = DB::selectOne("
                    SELECT 1 FROM information_schema.views
                    WHERE table_schema = DATABASE() AND table_name = 'productpriceview'
                ");
                $hasProductPriceView = !empty($hasProductPriceView);
            } catch (\Exception $e) {
                $hasProductPriceView = false;
            }

            // Build products query - filter by discount > 0
            $productsQuery = DB::table('products as p')
                ->select([
                    DB::raw($locale == 'ar' ? 'p.shortdescrAR as title' : 'p.shortdescr as title'),
                    'p.productid',
                    'p.productcode',
                    'p.price',
                    'p.photo1',
                    'c.categorycode',
                    DB::raw("IFNULL((SELECT SUM(qty) FROM productsfilter WHERE fkproductid=p.productid AND productsfilter.filtercode='size'), 0) as qty")
                ])
                ->distinct()
                ->where('p.ispublished', 1);

            if ($hasProductPriceView) {
                $column = \App\Helpers\TenantHelper::getProductPriceViewColumn();
                $productsQuery->addSelect(['pp.discount', 'pp.sellingprice'])
                    ->join('productpriceview as pp', "pp.{$column}", '=', 'p.productid')
                    ->where('pp.discount', '>', 0);
            } else {
                $productsQuery->addSelect([
                    DB::raw('COALESCE(p.discount, 0) as discount'),
                    DB::raw('COALESCE(p.sellingprice, p.price) as sellingprice')
                ])
                    ->where('p.discount', '>', 0);
            }

            if ($customerId) {
                $productsQuery->addSelect([DB::raw('IFNULL(w.wishlistid, 0) as wishlistid')]);
            } else {
                $productsQuery->addSelect([DB::raw('0 as wishlistid')]);
            }

            $productsQuery->leftJoin('category as c', 'c.categoryid', '=', 'p.fkcategoryid')
                ->where('c.ispublished', 1);

            // Filter by category if specified
            if ($category) {
                $productsQuery->where('c.categorycode', $category);
            }

            // Add wishlist join if customer is logged in
            if ($customerId) {
                $productsQuery->leftJoin('wishlist as w', function ($join) use ($customerId) {
                    $join->on('p.productid', '=', 'w.fkproductid')
                        ->where('w.fkcustomerid', '=', $customerId);
                });
            }

            // Apply color filter
            $colorIds = [];
            if ($color) {
                $productsQuery->join('productsfilter as pfcolor', function ($join) {
                    $join->on('p.productid', '=', 'pfcolor.fkproductid')
                        ->where('pfcolor.filtercode', '=', 'color');
                });
                $colorIds = DB::table('filtervalues')
                    ->where('filtervaluecode', $color)
                    ->where('fkfilterid', 2)
                    ->pluck('filtervalueid')
                    ->toArray();
                if (!empty($colorIds)) {
                    $productsQuery->whereIn('pfcolor.fkfiltervalueid', $colorIds);
                }
            }

            // Apply size filter
            $sizeIds = [];
            if ($size) {
                $productsQuery->join('productsfilter as pfsize', function ($join) {
                    $join->on('p.productid', '=', 'pfsize.fkproductid')
                        ->where('pfsize.filtercode', '=', 'size');
                });
                $sizeIds = DB::table('filtervalues')
                    ->where('filtervaluecode', $size)
                    ->where('fkfilterid', 3)
                    ->pluck('filtervalueid')
                    ->toArray();
                if (!empty($sizeIds)) {
                    $productsQuery->whereIn('pfsize.fkfiltervalueid', $sizeIds)
                        ->where('pfsize.qty', '>', 0);
                }
            }

            // Apply price filter
            // Note: Price filter values are in BASE currency (not converted), matching CI behavior
            $sellingPriceColumn = $hasProductPriceView ? 'pp.sellingprice' : DB::raw('COALESCE(p.sellingprice, p.price)');
            if ($price) {
                $prices = explode('-', $price);
                if (count($prices) == 1) {
                    $priceValue = $prices[0];
                    if ($price == 5) {
                        $productsQuery->where($sellingPriceColumn, '<=', $priceValue);
                    } else {
                        $productsQuery->where($sellingPriceColumn, '>', $priceValue);
                    }
                } else if (count($prices) == 2) {
                    $minPrice = $prices[0];
                    $maxPrice = $prices[1];
                    $productsQuery->where($sellingPriceColumn, '>=', $minPrice)
                        ->where($sellingPriceColumn, '<', $maxPrice);
                }
            }

            // Apply sorting
            if ($sortby == 'lowtohigh') {
                $sortColumn = $hasProductPriceView ? 'pp.sellingprice' : DB::raw('COALESCE(p.sellingprice, p.price)');
                $productsQuery->orderBy($sortColumn, 'asc');
            } else if ($sortby == 'hightolow') {
                $sortColumn = $hasProductPriceView ? 'pp.sellingprice' : DB::raw('COALESCE(p.sellingprice, p.price)');
                $productsQuery->orderBy($sortColumn, 'desc');
            } else {
                $productsQuery->orderBy($locale == 'ar' ? 'p.shortdescrAR' : 'p.shortdescr', 'asc')
                    ->orderBy('p.productid', 'desc');
            }

            // Get total count
            $productcnt = $productsQuery->count();
            $totalpage = ceil($productcnt / $perPage);

            // Get products with pagination
            if ($page == 1) {
                $products = $productsQuery->limit($perPage * $page)->get();
            } else {
                $offset = ($page - 1) * $perPage;
                $products = $productsQuery->offset($offset)->limit($perPage)->get();
            }

            // Get subcategories (all categories with sale products)
            $subcategories = DB::table('category as c')
                ->select([
                    $locale == 'ar' ? 'c.categoryAR as category' : 'c.category',
                    'c.categoryid',
                    'c.categorycode',
                    'c.parentid',
                    DB::raw("(SELECT COUNT(*) FROM products WHERE ispublished=1 AND discount>0 AND fkcategoryid=c.categoryid) as productcnt")
                ])
                ->distinct()
                ->join('products as p', 'p.fkcategoryid', '=', 'c.categoryid')
                ->where('p.ispublished', 1)
                ->where('c.ispublished', 1);

            if ($hasProductPriceView) {
                $column = \App\Helpers\TenantHelper::getProductPriceViewColumn();
                $subcategories->join('productpriceview as pp', "pp.{$column}", '=', 'p.productid')
                    ->where('pp.discount', '>', 0);
            } else {
                $subcategories->where('p.discount', '>', 0);
            }

            $subcategories = $subcategories->orderBy('c.parentid')
                ->orderBy('c.displayorder')
                ->get();

            // Get price range, colors, sizes (simplified - can be enhanced later)
            $pricerange = [];
            $colorsArr = [];
            $sizesArr = [];

            return [
                'category' => null,
                'products' => $products,
                'subcategory' => $subcategories,
                'productcnt' => $productcnt,
                'totalpage' => $totalpage,
                'colorsArr' => $colorsArr,
                'sizesArr' => $sizesArr,
                'pricerange' => $pricerange,
            ];
        } catch (\Exception $e) {
            \Log::error('CategoryController getSaleProductsWithFilters error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Prepare view data for category index page
     * Moves business logic from Blade templates to controller
     */
    protected function prepareViewData($collections, $locale, $metaTitle, $metaDescription, $additionalData = [])
    {
        // Calculate category title
        $categoryTitle = isset($collections['category']) && $collections['category']
            ? ($locale == 'ar' ? $collections['category']->categoryAR : $collections['category']->category)
            : ($metaTitle ?? __('All Products'));

        // Prepare filter data for sidefilter
        $filterData = $this->prepareFilterData(
            $collections,
            $locale,
            $additionalData['categoryCode'] ?? '',
            $additionalData['color'] ?? '',
            $additionalData['size'] ?? '',
            $additionalData['price'] ?? ''
        );

        return array_merge([
            'collections' => $collections,
            'category' => $collections['category'] ?? null,
            'products' => $collections['products'] ?? collect(),
            'subcategory' => $collections['subcategory'] ?? [],
            'colorsArr' => $collections['colorsArr'] ?? [],
            'sizesArr' => $collections['sizesArr'] ?? [],
            'pricerange' => $collections['pricerange'] ?? [],
            'productcnt' => $collections['productcnt'] ?? 0,
            'totalpage' => $collections['totalpage'] ?? 1,
            'categoryTitle' => $categoryTitle,
            'metaTitle' => $metaTitle,
            'metaDescription' => $metaDescription,
        ], $additionalData, $filterData);
    }

    /**
     * Prepare filter data for sidefilter view
     * Processes query parameters and prepares arrays for filter checking
     */
    protected function prepareFilterData($collections, $locale, $categoryCode, $color, $size, $price)
    {
        // Process color query string into array
        $colors_qry = $color;
        $arrColor = [];
        if (!empty($colors_qry)) {
            $arrColor = array_filter(array_map('trim', explode(',', $colors_qry)));
        }

        // Process size query string into array
        $sizes_qry = $size;
        $arrSize = [];
        if (!empty($sizes_qry)) {
            $arrSize = array_filter(array_map('trim', explode(',', $sizes_qry)));
        }

        // Price query string
        $price_qry = $price;

        return [
            'collections' => $collections,
            'locale' => $locale,
            'categoryCode' => $categoryCode,
            'colors_qry' => $colors_qry,
            'sizes_qry' => $sizes_qry,
            'price_qry' => $price_qry,
            'arrColor' => $arrColor,
            'arrSize' => $arrSize,
        ];
    }
}