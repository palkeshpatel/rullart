<?php

namespace App\Http\Controllers\Frontend;

use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends FrontendController
{
    public function show($slug)
    {
        $locale = app()->getLocale();
        
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
        
        return view('frontend.page.show', compact('page', 'metaTitle', 'metaDescription', 'metaKeywords'));
    }
    
    public function about()
    {
        return $this->show('aboutus');
    }
    
    public function contact()
    {
        return $this->show('contactus');
    }
    
    public function shipping()
    {
        return $this->show('shipping');
    }
}

