<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PageController extends Controller
{
    /**
     * Show the form for editing the specified page.
     */
    public function edit(Request $request, $pagename = 'home')
    {
        $page = Page::where('pagename', $pagename)->first();

        // If page doesn't exist, create a new one
        if (!$page) {
            $page = new Page();
            $page->pagename = $pagename;
            
            // Set default titles based on pagename
            $defaultTitles = [
                'home' => 'Welcome Text',
                'aboutus' => 'About Us',
                'corporate-gift' => 'Corporate Gifts',
                'franchises' => 'Franchise',
                'contactus' => 'Contact Us',
                'shipping' => 'Shipping',
                'newsletter' => 'Newsletter',
                'terms' => 'Terms & Conditions',
            ];
            
            $page->pagetitle = $defaultTitles[$pagename] ?? ucfirst(str_replace('-', ' ', $pagename));
            $page->pagetitleAR = '';
            $page->details = '';
            $page->detailsAR = '';
            $page->published = 1;
        }

        // Map pagename to view name
        $viewMap = [
            'corporate-gift' => 'corporate-gift',
            'terms' => 'terms',
        ];
        
        $viewName = $viewMap[$pagename] ?? str_replace('-', '_', $pagename);
        
        return view('admin.pages.' . $viewName, compact('page'));
    }

    /**
     * Update the specified page.
     */
    public function update(Request $request, $pagename = 'home')
    {
        // Fix for PUT requests with FormData
        if (
            $request->isMethod('put') && empty($request->all()) &&
            $request->header('Content-Type') &&
            str_contains($request->header('Content-Type'), 'multipart/form-data')
        ) {
            $content = $request->getContent();
            $boundary = null;

            if (preg_match('/boundary=([^;]+)/', $request->header('Content-Type'), $matches)) {
                $boundary = '--' . trim($matches[1]);
            }

            if ($boundary && $content) {
                $parts = explode($boundary, $content);
                $parsedData = [];

                foreach ($parts as $part) {
                    if (preg_match('/Content-Disposition:\s*form-data;\s*name="([^"]+)"\s*\r?\n\r?\n(.*?)(?=\r?\n--|$)/s', $part, $matches)) {
                        $fieldName = $matches[1];
                        $fieldValue = trim($matches[2], "\r\n");

                        if ($fieldName !== '_method') {
                            $parsedData[$fieldName] = $fieldValue;
                        }
                    }
                }

                if (!empty($parsedData)) {
                    $request->merge($parsedData);
                }
            }
        }

        $validated = $request->validate([
            'pagetitle' => 'required|string|max:200',
            'pagetitleAR' => 'nullable|string|max:200',
            'details' => 'nullable|string',
            'detailsAR' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'metatitle' => 'nullable|string|max:200',
            'metakeyword' => 'nullable|string|max:500',
            'metadescription' => 'nullable|string|max:1500',
            'published' => 'nullable',
        ], [
            'pagetitle.required' => 'Page Title (EN) is required.',
            'photo.image' => 'Photo must be an image file.',
        ]);

        $page = Page::where('pagename', $pagename)->first();

        if (!$page) {
            $page = new Page();
            $page->pagename = $pagename;
        }

        $validated['published'] = $request->has('published') ? 1 : 0;
        $validated['updateddate'] = now();
        $validated['fkuserid'] = auth()->id() ?? 1;

        // Handle file upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($page->photo && file_exists(public_path('uploads/pages/' . $page->photo))) {
                unlink(public_path('uploads/pages/' . $page->photo));
            }
            $photo = $request->file('photo');
            $photoName = time() . '_' . $photo->getClientOriginalName();
            $photo->move(public_path('uploads/pages'), $photoName);
            $validated['photo'] = $photoName;
        }

        $page->fill($validated);
        $page->save();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Page updated successfully',
                'data' => $page
            ]);
        }

        // Map pagename to route name
        $routeMap = [
            'corporate-gift' => 'pages.corporate-gift',
            'aboutus' => 'pages.aboutus',
            'franchises' => 'pages.franchises',
            'contactus' => 'pages.contactus',
            'shipping' => 'pages.shipping',
            'newsletter' => 'pages.newsletter',
            'terms' => 'pages.terms',
            'home' => 'pages.home',
        ];
        
        $routeName = $routeMap[$pagename] ?? 'pages.home';
        
        return redirect()->route($routeName)
            ->with('success', 'Page updated successfully');
    }
}

