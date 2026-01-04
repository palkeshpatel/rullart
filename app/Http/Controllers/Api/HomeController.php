<?php

namespace App\Http\Controllers\Api;

use App\Repositories\HomeRepository;
use Illuminate\Http\Request;

class HomeController extends ApiController
{
    protected $homeRepository;

    public function __construct(HomeRepository $homeRepository)
    {
        $this->homeRepository = $homeRepository;
    }

    /**
     * Get home page data
     * GET /{locale}/api/home/get?customerid=0&productview=
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function get(Request $request)
    {
        $locale = $this->getLocale();
        $customerId = $this->getCustomerId();
        $productView = $request->get('productview', '');

        // Get home gallery
        $homeGallery = $this->homeRepository->getHomeGallery($locale);

        // Process home gallery links - remove full URLs, keep only path
        foreach ($homeGallery as $item) {
            if (!empty($item->link)) {
                $link = $item->link;
                $link = str_replace('https://www.rullart.com/en/category/', '', $link);
                $link = str_replace('https://www.rullart.com/ar/category/', '', $link);
                $link = str_replace('https://www.rullart.com/en/', '', $link);
                $link = str_replace('https://www.rullart.com/ar/', '', $link);
                $item->link = $link;
            }
            // Clear title and description for API (as per CI implementation)
            $item->title = '';
            $item->titleAR = '';
            $item->descr = '';
            $item->descrAR = '';
        }

        // Get popular products
        $popularProducts = $this->homeRepository->getPopularProducts($locale);

        // Add 'thumb-' prefix to photo1 (as per CI implementation)
        foreach ($popularProducts as $product) {
            if (!empty($product->photo1)) {
                $product->photo1 = 'thumb-' . $product->photo1;
            }
        }

        $data = [
            'homegallery' => $homeGallery,
            'popularproducts' => $popularProducts,
        ];

        return $this->success($data);
    }

    /**
     * Get shop by categories
     * GET /{locale}/api/home/shopby?customerid=0
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function shopby(Request $request)
    {
        $locale = $this->getLocale();
        $customerId = $this->getCustomerId();

        // Get main categories
        $categories = \App\Models\Category::where('ispublished', 1)
            ->where('parentid', 0)
            ->where('categoryid', '!=', 77)
            ->where('categoryid', '!=', 80)
            ->orderBy('displayorder', 'asc')
            ->get()
            ->map(function ($cat) use ($locale) {
                return (object)[
                    'category' => $locale == 'ar' ? $cat->categoryAR : $cat->category,
                    'categoryid' => $cat->categoryid,
                    'categorycode' => $cat->categorycode,
                    'photo' => $cat->photo ?? '',
                ];
            });

        // Add "All" option
        if (!empty($categories)) {
            $categories->push((object)[
                'category' => __('All'),
                'categoryid' => '0',
                'categorycode' => 'all',
                'photo' => '',
            ]);
        }

        $menuData = [];

        // BY CATEGORIES
        $menuData[0] = (object)[
            'menuname' => __('BY CATEGORIES'),
            'menulist' => $categories,
            'menuid' => 1,
        ];

        // SALE categories
        $saleCategories = \App\Models\Category::where('ispublished', 1)
            ->whereHas('products', function ($query) {
                $query->where('ispublished', 1);
            })
            ->whereHas('products', function ($query) {
                $query->where('discount', '>', 0);
            })
            ->orderBy('displayorder', 'asc')
            ->get()
            ->map(function ($cat) use ($locale) {
                return (object)[
                    'category' => $locale == 'ar' ? $cat->categoryAR : $cat->category,
                    'categoryid' => $cat->categoryid,
                    'categorycode' => $cat->categorycode,
                    'photo' => $cat->photo ?? '',
                ];
            });

        if (!empty($saleCategories)) {
            $saleCategories->prepend((object)[
                'category' => __('All'),
                'categoryid' => '0',
                'categorycode' => 'all',
                'photo' => '',
            ]);
        }

        $menuData[1] = (object)[
            'menuname' => __('SALE'),
            'menulist' => $saleCategories,
            'menuid' => 2,
        ];

        // WHAT'S NEW categories
        $whatsNewCategories = \App\Models\Category::where('ispublished', 1)
            ->whereHas('products', function ($query) {
                $query->where('ispublished', 1)
                    ->where('isnew', 1);
            })
            ->orderBy('displayorder', 'asc')
            ->get()
            ->map(function ($cat) use ($locale) {
                return (object)[
                    'category' => $locale == 'ar' ? $cat->categoryAR : $cat->category,
                    'categoryid' => $cat->categoryid,
                    'categorycode' => $cat->categorycode,
                    'photo' => $cat->photo ?? '',
                ];
            });

        if (!empty($whatsNewCategories)) {
            $whatsNewCategories->prepend((object)[
                'category' => __('All'),
                'categoryid' => '0',
                'categorycode' => 'all',
                'photo' => '',
            ]);
        }

        $menuData[2] = (object)[
            'menuname' => __('WHATS NEW'),
            'menulist' => $whatsNewCategories,
            'menuid' => 3,
        ];

        return $this->success($menuData);
    }
}