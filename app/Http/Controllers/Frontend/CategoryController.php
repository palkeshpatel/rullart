<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends FrontendController
{
    public function index($locale, $categoryCode)
    {
        \Log::info("CategoryController index called");
        \Log::info("Request URL: " . request()->fullUrl());
        \Log::info("Request Path: " . request()->path());
        \Log::info("Route parameters: " . json_encode(request()->route()->parameters()));

        // Get categoryCode from route parameters directly to ensure correct value
        // Laravel binds route parameters by position, so we need to get it from route
        $categoryCode = request()->route('categoryCode') ?? $categoryCode;
        \Log::info("categoryCode from route: " . request()->route('categoryCode'));
        \Log::info("categoryCode parameter value: {$categoryCode}");

        // Use locale from route parameter
        $locale = $locale ?? app()->getLocale();

        $category = Category::where('categorycode', $categoryCode)
            ->where('ispublished', 1)
            ->first();

        \Log::info("Category found: " . ($category ? "Yes - ID: {$category->categoryid}, Code: {$category->categorycode}" : "No"));

        if (!$category) {
            \Log::error("Category not found: {$categoryCode}");
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
            'categoryCode' => $categoryCode,
            'isall' => false,
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
                return false;
            }

            // Check if productpriceview exists (it's a VIEW, not a TABLE)
            try {
                $hasProductPriceView = DB::selectOne("
                    SELECT 1
                    FROM information_schema.views
                    WHERE table_schema = DATABASE()
                      AND table_name = 'productpriceview'
                ");
                $hasProductPriceView = !empty($hasProductPriceView);
            } catch (\Exception $e) {
                $hasProductPriceView = false;
            }
            \Log::info("productpriceview exists: " . ($hasProductPriceView ? "Yes" : "No"));

            if (!$hasProductPriceView) {
                \Log::error('productpriceview view does not exist');
                return false;
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
                $productsQuery->addSelect(['pp.discount', 'pp.sellingprice'])
                    ->join('productpriceview as pp', 'pp.kproductid', '=', 'p.productid');
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
                // Get color IDs from filtervaluecode
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
            if ($size) {
                $productsQuery->join('productsfilter as pfsize', function ($join) {
                    $join->on('p.productid', '=', 'pfsize.fkproductid')
                        ->where('pfsize.filtercode', '=', 'size');
                });
                // Get size IDs from filtervaluecode
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
            if ($price) {
                $prices = explode('-', $price);
                $sellingPriceColumn = $hasProductPriceView ? 'pp.sellingprice' : DB::raw('COALESCE(p.sellingprice, p.price)');
                if (count($prices) == 1) {
                    $priceValue = $prices[0] / $currencyRate;
                    if ($price == 5) {
                        $productsQuery->where($sellingPriceColumn, '<=', $priceValue);
                    } else {
                        $productsQuery->where($sellingPriceColumn, '>', $priceValue);
                    }
                } else if (count($prices) == 2) {
                    $minPrice = $prices[0] / $currencyRate;
                    $maxPrice = $prices[1] / $currencyRate;
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
            } catch (\Exception $e) {
                \Log::error("Error in products query: " . $e->getMessage());
                \Log::error("Query: " . $productsQuery->toSql());
                throw $e;
            }

            // Get price range
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
                    $colorsQuery->join('productpriceview as pp', 'pp.kproductid', '=', 'p.productid');
                    $sellingPriceColumn = 'pp.sellingprice';
                } else {
                    $sellingPriceColumn = DB::raw('COALESCE(p.sellingprice, p.price)');
                }
                if (count($prices) == 1) {
                    $priceValue = $prices[0] / $currencyRate;
                    if ($price == 5) {
                        $colorsQuery->where($sellingPriceColumn, '<=', $priceValue);
                    } else {
                        $colorsQuery->where($sellingPriceColumn, '>', $priceValue);
                    }
                } else if (count($prices) == 2) {
                    $minPrice = $prices[0] / $currencyRate;
                    $maxPrice = $prices[1] / $currencyRate;
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
                    $sizesQuery->join('productpriceview as pp', 'pp.kproductid', '=', 'p.productid');
                    $sellingPriceColumn = 'pp.sellingprice';
                } else {
                    $sellingPriceColumn = DB::raw('COALESCE(p.sellingprice, p.price)');
                }
                if (count($prices) == 1) {
                    $priceValue = $prices[0] / $currencyRate;
                    if ($price == 5) {
                        $sizesQuery->where($sellingPriceColumn, '<=', $priceValue);
                    } else {
                        $sizesQuery->where($sellingPriceColumn, '>', $priceValue);
                    }
                } else if (count($prices) == 2) {
                    $minPrice = $prices[0] / $currencyRate;
                    $maxPrice = $prices[1] / $currencyRate;
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
            \Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            \Log::error($e->getTraceAsString());
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
                        $decimal = $currencyCode == "KWD" ? 3 : 2;
                        $convertedPrice = number_format($price * $currencyRate, $decimal);
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
                        $decimal = $currencyCode == "KWD" ? 3 : 2;
                        $convertedPrice = number_format($price * $currencyRate, $decimal);
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
                        $decimal = $currencyCode == "KWD" ? 3 : 2;
                        $convertedLow = number_format($lowLimit * $currencyRate, $decimal);
                        $convertedHigh = number_format($highLimit * $currencyRate, $decimal);
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
            $query->leftJoin('productpriceview as pp', 'pp.kproductid', '=', 'p.productid')
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
            $query->leftJoin('productpriceview as pp', 'pp.kproductid', '=', 'p.productid')
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
            $query->leftJoin('productpriceview as pp', 'pp.kproductid', '=', 'p.productid')
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
