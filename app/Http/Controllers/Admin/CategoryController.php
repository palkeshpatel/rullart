<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        // Filter by parent category
        if ($request->filled('parent_category') && $request->parent_category !== '' && $request->parent_category !== '--Parent--') {
            if ($request->parent_category == '0') {
                $query->where('parentid', 0)->orWhereNull('parentid');
            } else {
                $query->where('parentid', $request->parent_category);
            }
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('category', 'like', "%{$search}%")
                    ->orWhere('categoryAR', 'like', "%{$search}%")
                    ->orWhere('categorycode', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 25);
        $categories = $query->orderBy('displayorder', 'asc')->paginate($perPage);

        // Get parent categories for dropdown
        $parentCategories = Category::where('parentid', 0)->orWhereNull('parentid')->orderBy('category')->get();

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.partials.categories-table', compact('categories'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $categories])->render(),
            ]);
        }

        return view('admin.category.index', compact('categories', 'parentCategories'));
    }

    public function create(Request $request)
    {
        // Get parent categories for dropdown
        $parentCategories = Category::where('parentid', 0)->orWhereNull('parentid')->orderBy('category')->get();

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.category.partials.category-form', ['category' => null, 'parentCategories' => $parentCategories])->render(),
            ]);
        }

        return view('admin.category.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'categoryAR' => 'required|string|max:255',
            'categorycode' => [
                'required',
                'string',
                'max:255',
                Rule::unique('category', 'categorycode')
            ],
            'parentid' => 'required|integer',
            'metatitle' => 'nullable|string|max:500',
            'metatitleAR' => 'nullable|string|max:500',
            'metakeyword' => 'nullable|string|max:1000',
            'metakeywordAR' => 'nullable|string|max:1000',
            'metadescr' => 'nullable|string|max:1500',
            'metadescrAR' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'photo_mobile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'ispublished' => 'nullable',
            'showmenu' => 'nullable',
            'displayorder' => 'nullable|integer',
        ], [
            'category.required' => 'Category name (EN) is required.',
            'categoryAR.required' => 'Category name (AR) is required.',
            'categorycode.required' => 'Category code is required.',
            'categorycode.unique' => 'This category code already exists. Please choose a different code.',
            'parentid.required' => 'Parent category is required.',
            'parentid.integer' => 'Parent category must be a valid selection.',
            'photo.image' => 'Desktop photo must be an image.',
            'photo.max' => 'Desktop photo must not exceed 5MB.',
            'photo_mobile.image' => 'Mobile photo must be an image.',
            'photo_mobile.max' => 'Mobile photo must not exceed 5MB.',
        ]);

        // Validate parentid exists if not 0
        if ($validated['parentid'] != 0) {
            $parentExists = Category::where('categoryid', $validated['parentid'])->exists();
            if (!$parentExists) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected parent category does not exist.',
                        'errors' => ['parentid' => ['Selected parent category does not exist.']]
                    ], 422);
                }
                return back()->withErrors(['parentid' => 'Selected parent category does not exist.'])->withInput();
            }
        }

        // Handle boolean fields
        $validated['ispublished'] = $request->has('ispublished') ? 1 : 0;
        $validated['showmenu'] = $request->has('showmenu') ? 1 : 0;
        $validated['displayorder'] = $validated['displayorder'] ?? 0;
        $validated['updatedby'] = auth()->id() ?? 1;
        $validated['updateddate'] = now();

        // Handle file uploads
        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $photoName = time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
            $photoPath = $photo->storeAs('category', $photoName, 'public');
            $validated['photo'] = $photoName;
        }

        if ($request->hasFile('photo_mobile')) {
            $photoMobile = $request->file('photo_mobile');
            $photoMobileName = time() . '_' . uniqid() . '_mobile.' . $photoMobile->getClientOriginalExtension();
            $photoMobilePath = $photoMobile->storeAs('category', $photoMobileName, 'public');
            $validated['photo_mobile'] = $photoMobileName;
        }

        try {
            $category = Category::create($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Category created successfully',
                    'data' => $category
                ]);
            }

            return redirect()->route('admin.category')
                ->with('success', 'Category created successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database constraint violations
            $errorMessage = 'An error occurred while saving the category.';
            $errorField = 'categorycode';

            if ($e->getCode() == 23000) {
                if (strpos($e->getMessage(), 'categorycode') !== false) {
                    $errorMessage = 'This category code already exists. Please choose a different code.';
                    $errorField = 'categorycode';
                } else {
                    $errorMessage = 'A database constraint violation occurred. Please check your input.';
                }
            } else {
                Log::error('Category creation error: ' . $e->getMessage());
                $errorMessage = 'An error occurred while saving the category: ' . $e->getMessage();
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => [$errorField => [$errorMessage]]
                ], 422);
            }

            return back()->withErrors([$errorField => $errorMessage])->withInput();
        } catch (\Exception $e) {
            Log::error('Category creation error: ' . $e->getMessage());

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An unexpected error occurred: ' . $e->getMessage(),
                    'errors' => ['general' => ['An unexpected error occurred.']]
                ], 422);
            }

            return back()->withErrors(['general' => 'An unexpected error occurred.'])->withInput();
        }
    }

    public function show(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.category.partials.category-view', compact('category'))->render(),
            ]);
        }

        return view('admin.category.show', compact('category'));
    }

    public function edit(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        // Get parent categories for dropdown
        $parentCategories = Category::where('parentid', 0)->orWhereNull('parentid')
            ->where('categoryid', '!=', $id) // Exclude current category from parent options
            ->orderBy('category')->get();

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.category.partials.category-form', compact('category', 'parentCategories'))->render(),
            ]);
        }

        return view('admin.category.edit', compact('category', 'parentCategories'));
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        // Fix for PUT requests with FormData - PHP doesn't populate $_POST for PUT
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
            'category' => 'required|string|max:255',
            'categoryAR' => 'required|string|max:255',
            'categorycode' => [
                'required',
                'string',
                'max:255',
                Rule::unique('category', 'categorycode')->ignore($category->categoryid, 'categoryid')
            ],
            'parentid' => 'required|integer',
            'metatitle' => 'nullable|string|max:500',
            'metatitleAR' => 'nullable|string|max:500',
            'metakeyword' => 'nullable|string|max:1000',
            'metakeywordAR' => 'nullable|string|max:1000',
            'metadescr' => 'nullable|string|max:1500',
            'metadescrAR' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'photo_mobile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'ispublished' => 'nullable',
            'showmenu' => 'nullable',
            'displayorder' => 'nullable|integer',
        ], [
            'category.required' => 'Category name (EN) is required.',
            'categoryAR.required' => 'Category name (AR) is required.',
            'categorycode.required' => 'Category code is required.',
            'categorycode.unique' => 'This category code already exists. Please choose a different code.',
            'parentid.required' => 'Parent category is required.',
            'parentid.integer' => 'Parent category must be a valid selection.',
            'photo.image' => 'Desktop photo must be an image.',
            'photo.max' => 'Desktop photo must not exceed 5MB.',
            'photo_mobile.image' => 'Mobile photo must be an image.',
            'photo_mobile.max' => 'Mobile photo must not exceed 5MB.',
        ]);

        // Validate parentid exists if not 0
        if ($validated['parentid'] != 0) {
            $parentExists = Category::where('categoryid', $validated['parentid'])->exists();
            if (!$parentExists) {
                if ($request->ajax() || $request->expectsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Selected parent category does not exist.',
                        'errors' => ['parentid' => ['Selected parent category does not exist.']]
                    ], 422);
                }
                return back()->withErrors(['parentid' => 'Selected parent category does not exist.'])->withInput();
            }
        }

        // Handle boolean fields
        $validated['ispublished'] = $request->has('ispublished') ? 1 : 0;
        $validated['showmenu'] = $request->has('showmenu') ? 1 : 0;
        $validated['displayorder'] = $validated['displayorder'] ?? 0;

        try {
            $category->update($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Category updated successfully',
                    'data' => $category
                ]);
            }

            return redirect()->route('admin.category')
                ->with('success', 'Category updated successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database constraint violations
            $errorMessage = 'An error occurred while updating the category.';
            $errorField = 'categorycode';

            if ($e->getCode() == 23000) {
                if (strpos($e->getMessage(), 'categorycode') !== false) {
                    $errorMessage = 'This category code already exists. Please choose a different code.';
                    $errorField = 'categorycode';
                } else {
                    $errorMessage = 'A database constraint violation occurred. Please check your input.';
                }
            } else {
                Log::error('Category update error: ' . $e->getMessage());
                $errorMessage = 'An error occurred while updating the category: ' . $e->getMessage();
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => [$errorField => [$errorMessage]]
                ], 422);
            }

            return back()->withErrors([$errorField => $errorMessage])->withInput();
        } catch (\Exception $e) {
            Log::error('Category update error: ' . $e->getMessage());

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An unexpected error occurred: ' . $e->getMessage(),
                    'errors' => ['general' => ['An unexpected error occurred.']]
                ], 422);
            }

            return back()->withErrors(['general' => 'An unexpected error occurred.'])->withInput();
        }
    }

    public function destroy(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully'
            ]);
        }

        return redirect()->route('admin.category')
            ->with('success', 'Category deleted successfully');
    }
}