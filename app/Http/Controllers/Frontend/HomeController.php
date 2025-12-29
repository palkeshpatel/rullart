<?php

namespace App\Http\Controllers\Frontend;

use App\Models\HomeGallery;
use App\Models\Product;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends FrontendController
{
    public function index()
    {
        $locale = app()->getLocale();

        // Get home gallery images - Match CI get_data() method with locale-specific fields
        $homegallery = $this->getHomeGallery($locale);

        // Check if gallery has any videos (for carousel interval)
        $hasVideo = $homegallery->contains(function ($item) {
            return !empty($item->videourl);
        });

        // Get popular products
        $popular = $this->getPopularProducts($locale);

        // Get page meta data - match CI which uses 'pagename' not 'pagecode'
        $pages = Page::where('pagename', 'home')->first();

        $metaTitle = $pages->metatitle ?? 'Rullart - Premium Gifts & Accessories';
        $metaDescription = $pages->metadescription ?? 'We pride ourselves with gifts that are defined by their artistic craftsmanship and elegance.';
        $metaKeywords = $pages->metakeyword ?? '';

        $data = [
            'homegallery' => $homegallery,
            'popular' => $popular,
            'hasVideo' => $hasVideo,
            'metaTitle' => $metaTitle,
            'metaDescription' => $metaDescription,
            'metaKeywords' => $metaKeywords,
        ];

        return view('frontend.home.index', $data);
    }

    protected function getHomeGallery($locale)
    {
        // Match CI Homegallery_model->get_data() method
        if ($locale == 'ar') {
            $homegallery = HomeGallery::select(
                DB::raw('titleAR as title'),
                DB::raw('descrAR as descr'),
                'link',
                DB::raw('photo_ar as photo'),
                DB::raw('photo_mobile_ar as photo_mobile'),
                'displayorder',
                DB::raw("IFNULL(videourl, '') as videourl")
            )
                ->where('ispublished', 1)
                ->orderBy('displayorder', 'asc')
                ->get();
        } else {
            $homegallery = HomeGallery::select(
                'title',
                'titleAR',
                'descr',
                'descrAR',
                'link',
                'photo',
                'photo_mobile',
                'displayorder',
                DB::raw("IFNULL(videourl, '') as videourl")
            )
                ->where('ispublished', 1)
                ->orderBy('displayorder', 'asc')
                ->get();
        }

        return $homegallery;
    }

    protected function getPopularProducts($locale)
    {
        // Match CI get_popular() - products must have productsfilter entries with qty > 0
        // CI uses productpriceview for prices (pp.discount, pp.sellingprice)
        $selectFields = [
            'p.productid',
            $locale == 'ar' ? 'p.shortdescrAR as title' : 'p.shortdescr AS title',
            'p.productcode',
            'p.price',
            'p.photo1',
            $locale == 'ar' ? 'p.titleAR as shortdescr' : 'p.title as shortdescr',
            'c.categorycode',
            DB::raw("(select sum(qty) from productsfilter where fkproductid=p.productid and productsfilter.filtercode='size') as qty")
        ];

        // Add productpriceview fields if table exists
        if (DB::getSchemaBuilder()->hasTable('productpriceview')) {
            $selectFields[] = 'pp.discount';
            $selectFields[] = 'pp.sellingprice';
        } else {
            $selectFields[] = DB::raw('COALESCE(p.discount, 0) as discount');
            $selectFields[] = DB::raw('COALESCE(p.sellingprice, p.price) as sellingprice');
        }

        $query = DB::table('products as p')
            ->select($selectFields)
            ->join('category as c', 'p.fkcategoryid', '=', 'c.categoryid')
            ->join('productsfilter as pf', function ($join) {
                $join->on('p.productid', '=', 'pf.fkproductid')
                    ->where('pf.filtercode', '=', 'size');
            })
            ->where('p.ispublished', 1)
            ->where('p.ispopular', 1)
            ->where('c.ispublished', 1)
            ->where('pf.qty', '>', 0);

        // Join productpriceview if it exists
        if (DB::getSchemaBuilder()->hasTable('productpriceview')) {
            $query->leftJoin('productpriceview as pp', 'pp.fkproductid', '=', 'p.productid');
        }

        // CI uses RANDOM order, but Laravel uses inRandomOrder()
        $products = $query->inRandomOrder()
            ->limit(16)
            ->get();

        return $products;
    }
}