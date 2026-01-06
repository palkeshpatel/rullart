<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends FrontendController
{
    public function show($slug)
    {
        // Get locale from URL segment (most reliable) or session
        $locale = request()->segment(1);
        if (!in_array($locale, ['en', 'ar'])) {
            $locale = session('locale', app()->getLocale() ?: 'en');
        }
        
        // Ensure locale is set in application
        app()->setLocale($locale);
        session(['locale' => $locale]);

        // Match CI which uses 'pagename' not 'pagecode'
        // Note: pages table uses 'published' column (not 'ispublished')
        $page = Page::where('pagename', $slug)
            ->where('published', 1)
            ->first();

        if (!$page) {
            abort(404);
        }

        // Match CI column names: pagetitle, pagetitleAR (not title)
        $metaTitle = $page->metatitle ?? ($locale == 'ar' ? $page->pagetitleAR : $page->pagetitle);
        $metaDescription = $page->metadescription ?? '';
        $metaKeywords = $page->metakeyword ?? '';

        return view('frontend.page.show', compact('page', 'metaTitle', 'metaDescription', 'metaKeywords', 'locale'));
    }

    public function about()
    {
        // Get locale from URL segment (most reliable) or session
        $locale = request()->segment(1);
        if (!in_array($locale, ['en', 'ar'])) {
            $locale = session('locale', app()->getLocale() ?: 'en');
        }
        
        // Ensure locale is set in application
        app()->setLocale($locale);
        session(['locale' => $locale]);
        
        return $this->show('aboutus');
    }

    public function contact()
    {
        // Get locale from URL segment (most reliable) or session
        $locale = request()->segment(1);
        if (!in_array($locale, ['en', 'ar'])) {
            $locale = session('locale', app()->getLocale() ?: 'en');
        }
        
        // Ensure locale is set in application
        app()->setLocale($locale);
        session(['locale' => $locale]);

        // Match CI which uses 'pagename' not 'pagecode'
        // Note: pages table uses 'published' column (not 'ispublished')
        $page = Page::where('pagename', 'contactus')
            ->where('published', 1)
            ->first();

        if (!$page) {
            abort(404);
        }

        // Replace [FOLLOWUS] placeholder with social media icons
        // Get settings from parent controller
        $settingsArr = $this->settingsArr ?? [];

        // Build social media icons HTML
        $socialIcons = '<ul class="list-inline social-widget">';

        if (isset($settingsArr['Instagram URL']) && !empty($settingsArr['Instagram URL'])) {
            $socialIcons .= '<li><a class="instagram" href="' . htmlspecialchars($settingsArr['Instagram URL']) . '" target="_blank"><svg class="icon icon-instagram"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="/static/images/symbol-defs.svg#icon-instagram"></use></svg></a></li>';
        }

        if (isset($settingsArr['Facebook URL']) && !empty($settingsArr['Facebook URL'])) {
            $socialIcons .= '<li><a class="facebook" href="' . htmlspecialchars($settingsArr['Facebook URL']) . '" target="_blank"><svg class="icon icon-facebook"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="/static/images/symbol-defs.svg#icon-facebook"></use></svg></a></li>';
        }

        if (isset($settingsArr['Twitter URL']) && !empty($settingsArr['Twitter URL'])) {
            $socialIcons .= '<li><a class="twitter" href="' . htmlspecialchars($settingsArr['Twitter URL']) . '" target="_blank"><svg class="icon icon-twitter"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="/static/images/symbol-defs.svg#icon-twitter"></use></svg></a></li>';
        }

        $socialIcons .= '</ul>';

        // Replace [FOLLOWUS] placeholder in page details
        $page->details = str_replace('[FOLLOWUS]', $socialIcons, $page->details ?? '');
        if ($page->detailsAR) {
            $page->detailsAR = str_replace('[FOLLOWUS]', $socialIcons, $page->detailsAR);
        }

        // Match CI column names: pagetitle, pagetitleAR (not title)
        $metaTitle = $page->metatitle ?? ($locale == 'ar' ? $page->pagetitleAR : $page->pagetitle);
        $metaDescription = $page->metadescription ?? '';
        $metaKeywords = $page->metakeyword ?? '';

        return view('frontend.page.show', compact('page', 'metaTitle', 'metaDescription', 'metaKeywords', 'locale'));
    }

    public function shipping()
    {
        return $this->show('shipping');
    }
}