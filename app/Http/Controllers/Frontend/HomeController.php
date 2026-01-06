<?php

namespace App\Http\Controllers\Frontend;

use App\Repositories\HomeRepository;
use App\Models\Page;
use Illuminate\Http\Request;

class HomeController extends FrontendController
{
    protected $homeRepository;

    public function __construct(HomeRepository $homeRepository)
    {
        parent::__construct();
        $this->homeRepository = $homeRepository;
    }

    public function index()
    {
        // Get locale from URL segment (most reliable) or session
        $locale = request()->segment(1);
        if (!in_array($locale, ['en', 'ar'])) {
            $locale = session('locale', app()->getLocale() ?: 'en');
        }
        
        // Ensure locale is set in application
        app()->setLocale($locale);
        session(['locale' => $locale]);

        // Get home gallery images
        $homegallery = $this->homeRepository->getHomeGallery($locale);

        // Check if gallery has any videos (for carousel interval)
        $hasVideo = $homegallery->contains(function ($item) {
            return !empty($item->videourl);
        });

        // Get popular products
        $popular = $this->homeRepository->getPopularProducts($locale);

        // Get page meta data
        $pages = Page::where('pagename', 'home')->first();

        // Use Arabic fields when locale is Arabic
        if ($locale == 'ar') {
            $metaTitle = $pages->metatitle ?? $pages->pagetitleAR ?? 'Rullart - Premium Gifts & Accessories';
            $metaDescription = $pages->metadescription ?? $pages->detailsAR ?? 'نحن نمتاز بهدايا مصنوعة بجودة عالية وفخامة لا تضاهى';
            $metaKeywords = $pages->metakeyword ?? '';
        } else {
            $metaTitle = $pages->metatitle ?? $pages->pagetitle ?? 'Rullart - Premium Gifts & Accessories';
            $metaDescription = $pages->metadescription ?? $pages->details ?? 'We pride ourselves with gifts that are defined by their artistic craftsmanship and elegance.';
            $metaKeywords = $pages->metakeyword ?? '';
        }

        $data = [
            'locale' => $locale,
            'homegallery' => $homegallery,
            'popular' => $popular,
            'hasVideo' => $hasVideo,
            'metaTitle' => $metaTitle,
            'metaDescription' => $metaDescription,
            'metaKeywords' => $metaKeywords,
        ];

        return view('frontend.home.index', $data);
    }
}