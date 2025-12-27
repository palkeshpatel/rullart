<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeGallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class HomeGalleryController extends Controller
{
    public function index(Request $request)
    {
        $query = HomeGallery::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('titleAR', 'like', "%{$search}%");
            });
        }

        // Filter by published status
        if ($request->has('published') && $request->published !== '') {
            $query->where('ispublished', $request->published);
        }

        // Sorting
        $sortColumn = $request->get('sort', 'displayorder');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortColumn, $sortDirection);

        $perPage = $request->get('per_page', 25);
        $homeGalleries = $query->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.pages.partials.home-gallery.home-gallery-table', compact('homeGalleries'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $homeGalleries])->render(),
            ]);
        }

        return view('admin.pages.home-gallery', compact('homeGalleries'));
    }

    public function create(Request $request)
    {
        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.pages.partials.home-gallery.home-gallery-form', ['homeGallery' => null])->render(),
            ]);
        }

        return view('admin.pages.home-gallery-create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:250',
            'titleAR' => 'nullable|string|max:500',
            'descr' => 'nullable|string|max:5000',
            'descrAR' => 'nullable|string|max:1500',
            'link' => 'nullable|string|max:500',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'photo_mobile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'photo_ar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'photo_mobile_ar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'videourl' => 'nullable|string|max:500',
            'displayorder' => 'nullable|integer|min:0',
            'ispublished' => 'nullable',
        ], [
            'title.required' => 'Title(EN) is required.',
            'photo.image' => 'Photo must be an image file.',
            'photo_mobile.image' => 'Mobile photo must be an image file.',
        ]);

        // Validate URLs only if they are provided and not empty
        if (!empty($validated['link']) && !filter_var($validated['link'], FILTER_VALIDATE_URL)) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please enter a valid URL for the link field.',
                    'errors' => ['link' => ['Please enter a valid URL.']]
                ], 422);
            }
            return back()->withErrors(['link' => 'Please enter a valid URL.'])->withInput();
        }

        if (!empty($validated['videourl']) && !filter_var($validated['videourl'], FILTER_VALIDATE_URL)) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please enter a valid URL for the video URL field.',
                    'errors' => ['videourl' => ['Please enter a valid video URL.']]
                ], 422);
            }
            return back()->withErrors(['videourl' => 'Please enter a valid video URL.'])->withInput();
        }

        $validated['ispublished'] = $request->has('ispublished') ? 1 : 0;
        $validated['displayorder'] = $validated['displayorder'] ?? 0;
        $validated['updateddate'] = now()->format('Y-m-d');
        $validated['updatedby'] = auth()->id() ?? 1;

        // Create uploads directory if it doesn't exist
        $uploadPath = public_path('uploads/homegallery');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Handle file uploads
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoName = time() . '_' . $photo->getClientOriginalName();
            $photo->move($uploadPath, $photoName);
            $validated['photo'] = $photoName;
        }

        if ($request->hasFile('photo_mobile')) {
            $photoMobile = $request->file('photo_mobile');
            $photoMobileName = time() . '_mobile_' . $photoMobile->getClientOriginalName();
            $photoMobile->move($uploadPath, $photoMobileName);
            $validated['photo_mobile'] = $photoMobileName;
        }

        if ($request->hasFile('photo_ar')) {
            $photoAr = $request->file('photo_ar');
            $photoArName = time() . '_ar_' . $photoAr->getClientOriginalName();
            $photoAr->move($uploadPath, $photoArName);
            $validated['photo_ar'] = $photoArName;
        }

        if ($request->hasFile('photo_mobile_ar')) {
            $photoMobileAr = $request->file('photo_mobile_ar');
            $photoMobileArName = time() . '_mobile_ar_' . $photoMobileAr->getClientOriginalName();
            $photoMobileAr->move($uploadPath, $photoMobileArName);
            $validated['photo_mobile_ar'] = $photoMobileArName;
        }

        try {
            // Get the next ID if auto-increment is not working
            if (!isset($validated['homegalleryid'])) {
                $maxId = HomeGallery::max('homegalleryid') ?? 0;
                $validated['homegalleryid'] = $maxId + 1;
            }

            $homeGallery = HomeGallery::create($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Photo added successfully',
                    'data' => $homeGallery
                ]);
            }

            return redirect()->route('admin.home-gallery')
                ->with('success', 'Photo added successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            // Log the actual error for debugging
            Log::error('HomeGallery Store Error: ' . $e->getMessage());
            Log::error('HomeGallery Store Error Trace: ' . $e->getTraceAsString());

            $errorMessage = 'An error occurred while saving the photo.';
            
            // Provide more specific error messages
            if ($e->getCode() == 23000) {
                $errorMessage = 'A photo with this title already exists or there was a database constraint violation.';
            } elseif (str_contains($e->getMessage(), 'SQLSTATE[23000]')) {
                $errorMessage = 'Database error: Duplicate entry or constraint violation.';
            } elseif (str_contains($e->getMessage(), 'SQLSTATE[HY000]')) {
                $errorMessage = 'Database connection error. Please try again.';
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['title' => [$errorMessage]],
                    'debug' => config('app.debug') ? $e->getMessage() : null
                ], 422);
            }

            return back()->withErrors(['title' => $errorMessage])->withInput();
        } catch (\Exception $e) {
            // Catch any other exceptions
            Log::error('HomeGallery Store Exception: ' . $e->getMessage());
            Log::error('HomeGallery Store Exception Trace: ' . $e->getTraceAsString());

            $errorMessage = 'An unexpected error occurred while saving the photo.';

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['title' => [$errorMessage]],
                    'debug' => config('app.debug') ? $e->getMessage() : null
                ], 422);
            }

            return back()->withErrors(['title' => $errorMessage])->withInput();
        }
    }

    public function show(Request $request, $id)
    {
        $homeGallery = HomeGallery::findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.pages.partials.home-gallery.home-gallery-view', compact('homeGallery'))->render(),
            ]);
        }

        return view('admin.pages.home-gallery-view', compact('homeGallery'));
    }

    public function edit(Request $request, $id)
    {
        $homeGallery = HomeGallery::findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.pages.partials.home-gallery.home-gallery-form', compact('homeGallery'))->render(),
            ]);
        }

        return view('admin.pages.home-gallery-edit', compact('homeGallery'));
    }

    public function update(Request $request, $id)
    {
        $homeGallery = HomeGallery::findOrFail($id);

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
                'title' => 'required|string|max:250',
                'titleAR' => 'nullable|string|max:500',
                'descr' => 'nullable|string|max:5000',
                'descrAR' => 'nullable|string|max:1500',
                'link' => 'nullable|string|max:500',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'photo_mobile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'photo_ar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'photo_mobile_ar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'videourl' => 'nullable|string|max:500',
                'displayorder' => 'nullable|integer|min:0',
                'ispublished' => 'nullable',
            ], [
                'title.required' => 'Title(EN) is required.',
                'photo.image' => 'Photo must be an image file.',
                'photo_mobile.image' => 'Mobile photo must be an image file.',
            ]);

            // Validate URLs only if they are provided and not empty
            if (!empty($validated['link']) && !filter_var($validated['link'], FILTER_VALIDATE_URL)) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please enter a valid URL for the link field.',
                        'errors' => ['link' => ['Please enter a valid URL.']]
                    ], 422);
                }
                return back()->withErrors(['link' => 'Please enter a valid URL.'])->withInput();
            }

            if (!empty($validated['videourl']) && !filter_var($validated['videourl'], FILTER_VALIDATE_URL)) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please enter a valid URL for the video URL field.',
                        'errors' => ['videourl' => ['Please enter a valid video URL.']]
                    ], 422);
                }
                return back()->withErrors(['videourl' => 'Please enter a valid video URL.'])->withInput();
            }

            $validated['ispublished'] = $request->has('ispublished') ? 1 : 0;
            $validated['updateddate'] = now()->format('Y-m-d');
            $validated['updatedby'] = auth()->id() ?? 1;

        // Handle file uploads (only if new files are uploaded)
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($homeGallery->photo && file_exists(public_path('uploads/homegallery/' . $homeGallery->photo))) {
                unlink(public_path('uploads/homegallery/' . $homeGallery->photo));
            }
            $photo = $request->file('photo');
            $photoName = time() . '_' . $photo->getClientOriginalName();
            $photo->move(public_path('uploads/homegallery'), $photoName);
            $validated['photo'] = $photoName;
        }

        if ($request->hasFile('photo_mobile')) {
            if ($homeGallery->photo_mobile && file_exists(public_path('uploads/homegallery/' . $homeGallery->photo_mobile))) {
                unlink(public_path('uploads/homegallery/' . $homeGallery->photo_mobile));
            }
            $photoMobile = $request->file('photo_mobile');
            $photoMobileName = time() . '_mobile_' . $photoMobile->getClientOriginalName();
            $photoMobile->move(public_path('uploads/homegallery'), $photoMobileName);
            $validated['photo_mobile'] = $photoMobileName;
        }

        if ($request->hasFile('photo_ar')) {
            if ($homeGallery->photo_ar && file_exists(public_path('uploads/homegallery/' . $homeGallery->photo_ar))) {
                unlink(public_path('uploads/homegallery/' . $homeGallery->photo_ar));
            }
            $photoAr = $request->file('photo_ar');
            $photoArName = time() . '_ar_' . $photoAr->getClientOriginalName();
            $photoAr->move(public_path('uploads/homegallery'), $photoArName);
            $validated['photo_ar'] = $photoArName;
        }

        if ($request->hasFile('photo_mobile_ar')) {
            if ($homeGallery->photo_mobile_ar && file_exists(public_path('uploads/homegallery/' . $homeGallery->photo_mobile_ar))) {
                unlink(public_path('uploads/homegallery/' . $homeGallery->photo_mobile_ar));
            }
            $photoMobileAr = $request->file('photo_mobile_ar');
            $photoMobileArName = time() . '_mobile_ar_' . $photoMobileAr->getClientOriginalName();
            $photoMobileAr->move(public_path('uploads/homegallery'), $photoMobileArName);
            $validated['photo_mobile_ar'] = $photoMobileArName;
        }

        try {
            $homeGallery->update($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Photo updated successfully',
                    'data' => $homeGallery
                ]);
            }

            return redirect()->route('admin.home-gallery')
                ->with('success', 'Photo updated successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            $errorMessage = 'An error occurred while updating the photo.';

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['title' => [$errorMessage]]
                ], 422);
            }

            return back()->withErrors(['title' => $errorMessage])->withInput();
        }
    }

    public function destroy(Request $request, $id)
    {
        $homeGallery = HomeGallery::findOrFail($id);

        // Delete associated files
        if ($homeGallery->photo && file_exists(public_path('uploads/homegallery/' . $homeGallery->photo))) {
            unlink(public_path('uploads/homegallery/' . $homeGallery->photo));
        }
        if ($homeGallery->photo_mobile && file_exists(public_path('uploads/homegallery/' . $homeGallery->photo_mobile))) {
            unlink(public_path('uploads/homegallery/' . $homeGallery->photo_mobile));
        }
        if ($homeGallery->photo_ar && file_exists(public_path('uploads/homegallery/' . $homeGallery->photo_ar))) {
            unlink(public_path('uploads/homegallery/' . $homeGallery->photo_ar));
        }
        if ($homeGallery->photo_mobile_ar && file_exists(public_path('uploads/homegallery/' . $homeGallery->photo_mobile_ar))) {
            unlink(public_path('uploads/homegallery/' . $homeGallery->photo_mobile_ar));
        }

        $homeGallery->delete();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Photo deleted successfully'
            ]);
        }

        return redirect()->route('admin.home-gallery')
            ->with('success', 'Photo deleted successfully');
    }
}

