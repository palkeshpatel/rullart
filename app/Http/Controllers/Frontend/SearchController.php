<?php

namespace App\Http\Controllers\Frontend;

use App\Repositories\SearchRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends FrontendController
{
    protected $searchRepository;

    public function __construct(SearchRepository $searchRepository)
    {
        $this->searchRepository = $searchRepository;
    }

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
        $collections = $this->searchRepository->searchProducts(
            $keyword, 
            $categorycode, 
            $color, 
            $size, 
            $price, 
            $sortby, 
            $page, 
            $locale,
            $this->currencyRate
        );
        
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

    /**
     * AJAX endpoint for search product listing
     */
    public function prodlisting($locale)
    {
        $locale = $locale ?? app()->getLocale();
        $keyword = request()->get('keyword', '');
        
        if (empty($keyword)) {
            return response()->json('FALSE');
        }
        
        $sortby = request()->get('sortby', 'relevance');
        $color = request()->get('color', '');
        $size = request()->get('size', '');
        $price = request()->get('price', '');
        $categorycode = request()->get('category', '');
        $page = request()->get('page', 1);

        try {
            $collections = $this->searchRepository->searchProducts(
                $keyword, 
                $categorycode, 
                $color, 
                $size, 
                $price, 
                $sortby, 
                $page, 
                $locale,
                $this->currencyRate
            );

            if (!$collections || count($collections['products']) == 0) {
                return response()->json('FALSE');
            }

            // Get subcategories for search results
            $subcategories = $this->searchRepository->getSearchSubcategories($locale);
            $collections['subcategory'] = $subcategories;

            $sideFilterHtml = view('frontend.category.sidefilter', [
                'collections' => $collections,
                'locale' => $locale,
                'categoryCode' => $categorycode
            ])->render();
            $sideFilterHtml = str_replace(["\r", "\n", "\t"], '', $sideFilterHtml);

            return response()->json([
                'products' => $collections['products'],
                'subcategory' => $collections['subcategory'],
                'productcnt' => $collections['productcnt'],
                'totalpage' => $collections['totalpage'],
                'sidefilter' => $sideFilterHtml,
            ]);

        } catch (\Exception $e) {
            \Log::error('SearchController prodlisting error: ' . $e->getMessage());
            return response()->json('FALSE');
        }
    }
}

