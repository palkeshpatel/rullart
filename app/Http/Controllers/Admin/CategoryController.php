<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\UploadedFile;
use Exception;

class CategoryController extends Controller
{
    use ImageUploadTrait;
    public function index(Request $request)
    {
        // Check if this is a DataTables request
        if ($request->has('draw')) {
            return $this->getDataTablesData($request);
        }

        // Get parent categories for dropdown (exclude categories 77 and 80 like CI project)
        $parentCategories = Category::where(function ($query) {
            $query->where('parentid', 0)->orWhereNull('parentid');
        })
            ->where('categoryid', '!=', 77)
            ->where('categoryid', '!=', 80)
            ->orderBy('category')->get();

        // Return view for initial page load
        return view('admin.category.index', compact('parentCategories'));
    }

    /**
     * Get DataTables data for server-side processing
     */
    private function getDataTablesData(Request $request)
    {
        try {
            // Base query for counting
            $countQuery = Category::query();

            // Get total records count (before filtering)
            $totalRecords = $countQuery->count();

            // Build base query for data
            $query = Category::query();

            // Build count query for filtered results
            $filteredCountQuery = Category::query();

            // Filter by parent category
            $parentCategory = $request->input('parent_category');
            if (!empty($parentCategory) && $parentCategory !== '--Parent--') {
                if ($parentCategory == '0') {
                    $query->where(function ($q) {
                        $q->where('parentid', 0)->orWhereNull('parentid');
                    });
                    $filteredCountQuery->where(function ($q) {
                        $q->where('parentid', 0)->orWhereNull('parentid');
                    });
                } else {
                    $query->where('parentid', $parentCategory);
                    $filteredCountQuery->where('parentid', $parentCategory);
                }
            }

            // Get filtered count (after filters but before search)
            $filteredCount = $filteredCountQuery->count();

            // DataTables search (global search)
            $searchValue = $request->input('search.value', '');
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('category', 'like', "%{$searchValue}%")
                        ->orWhere('categoryAR', 'like', "%{$searchValue}%")
                        ->orWhere('categorycode', 'like', "%{$searchValue}%");
                });

                // Apply same search to count query
                $filteredCountQuery->where(function ($q) use ($searchValue) {
                    $q->where('category', 'like', "%{$searchValue}%")
                        ->orWhere('categoryAR', 'like', "%{$searchValue}%")
                        ->orWhere('categorycode', 'like', "%{$searchValue}%");
                });
            }

            // Get filtered count after search
            $filteredAfterSearch = $filteredCountQuery->count();

            // Ordering
            $orderColumnIndex = $request->input('order.0.column', 0);
            $orderDir = $request->input('order.0.dir', 'desc');

            $columns = [
                'category',
                'categoryAR',
                'ispublished',
                'displayorder',
                'updateddate',
                'categoryid' // For action column
            ];

            $orderColumn = $columns[$orderColumnIndex] ?? 'categoryid';
            $query->orderBy($orderColumn, $orderDir);

            // Pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 25);
            $categories = $query->skip($start)->take($length)->get();

            // Format data for DataTables
            $data = [];
            $categoryBaseUrl = url('/admin/category');
            foreach ($categories as $category) {
                $data[] = [
                    'category' => $category->category ?? '',
                    'categoryAR' => $category->categoryAR ?? 'N/A',
                    'isactive' => $category->ispublished ? 'Yes' : 'No',
                    'displayorder' => $category->displayorder ?? 0,
                    'updateddate' => $category->updateddate ? \Carbon\Carbon::parse($category->updateddate)->format('d/M/Y') : 'N/A',
                    'action' => $category->categoryid // For action buttons
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredAfterSearch,
                'data' => $data
            ]);
        } catch (Exception $e) {
            Log::error('Category DataTables Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'draw' => intval($request->input('draw', 1)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'An error occurred while loading data.'
            ], 500);
        }
    }

    public function create(Request $request)
    {
        // Get parent categories for dropdown (exclude categories 77 and 80 like CI project)
        $parentCategories = Category::where(function ($query) {
            $query->where('parentid', 0)->orWhereNull('parentid');
        })
            ->where('categoryid', '!=', 77)
            ->where('categoryid', '!=', 80)
            ->orderBy('category')->get();

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
            'parentid' => 'nullable|integer',
            'metatitle' => 'nullable|string|max:500',
            'metatitleAR' => 'nullable|string|max:500',
            'metakeyword' => 'nullable|string|max:1000',
            'metakeywordAR' => 'nullable|string|max:1000',
            'metadescr' => 'nullable|string|max:1500',
            'metadescrAR' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'photo_mobile' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'ispublished' => 'nullable',
            'showmenu' => 'nullable',
            'displayorder' => 'nullable|integer',
        ], [
            'category.required' => 'Category name (EN) is required.',
            'categoryAR.required' => 'Category name (AR) is required.',
            'parentid.integer' => 'Parent category must be a valid selection.',
            'photo.image' => 'Desktop photo must be an image.',
            'photo.max' => 'Desktop photo must not exceed 10MB.',
            'photo_mobile.image' => 'Mobile photo must be an image.',
            'photo_mobile.max' => 'Mobile photo must not exceed 10MB.',
        ]);

        // Generate categorycode from category name (EN)
        // Remove special characters, convert to lowercase, replace spaces with hyphens
        $categoryName = trim($validated['category']);
        $baseCode = strtolower($categoryName);

        // Remove Arabic and special characters, keep only alphanumeric and spaces
        $baseCode = preg_replace('/[^\p{L}\p{N}\s]+/u', '', $baseCode);

        // Replace spaces and multiple spaces with single hyphen
        $baseCode = preg_replace('/\s+/', '-', $baseCode);

        // Remove leading/trailing hyphens
        $baseCode = trim($baseCode, '-');

        // If still empty (e.g., only Arabic characters), use transliteration or fallback
        if (empty($baseCode)) {
            // Try to create a code from category name using transliteration
            $baseCode = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $categoryName));
            $baseCode = trim($baseCode, '-');

            // If still empty, use timestamp-based fallback
            if (empty($baseCode)) {
                $baseCode = 'category-' . time();
            }
        }

        // Ensure categorycode is unique
        $categorycode = $baseCode;
        $counter = 1;
        while (Category::where('categorycode', $categorycode)->exists()) {
            $categorycode = $baseCode . '-' . $counter;
            $counter++;
        }

        $validated['categorycode'] = $categorycode;

        // Set default parentid if not provided
        if (empty($validated['parentid'])) {
            $validated['parentid'] = 0;
        }

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

        // Handle file uploads using trait
        Log::info('CategoryController: Starting file upload process (store)', [
            'has_photo' => $request->hasFile('photo'),
            'has_photo_mobile' => $request->hasFile('photo_mobile'),
        ]);

        if ($request->hasFile('photo')) {
            Log::info('CategoryController: Uploading photo (store)', [
                'file_name' => $request->file('photo')->getClientOriginalName(),
                'file_size' => $request->file('photo')->getSize(),
                'mime_type' => $request->file('photo')->getMimeType(),
            ]);
            $validated['photo'] = $this->uploadImage($request->file('photo'), null, 'category');
            Log::info('CategoryController: Photo upload result (store)', [
                'uploaded_filename' => $validated['photo'],
            ]);
        }

        if ($request->hasFile('photo_mobile')) {
            Log::info('CategoryController: Uploading photo_mobile (store)', [
                'file_name' => $request->file('photo_mobile')->getClientOriginalName(),
                'file_size' => $request->file('photo_mobile')->getSize(),
                'mime_type' => $request->file('photo_mobile')->getMimeType(),
            ]);
            $validated['photo_mobile'] = $this->uploadImage($request->file('photo_mobile'), null, 'category');
            Log::info('CategoryController: Photo_mobile upload result (store)', [
                'uploaded_filename' => $validated['photo_mobile'],
            ]);
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

        // Get parent categories for dropdown (exclude categories 77 and 80 like CI project)
        $parentCategories = Category::where(function ($query) {
            $query->where('parentid', 0)->orWhereNull('parentid');
        })
            ->where('categoryid', '!=', $id) // Exclude current category from parent options
            ->where('categoryid', '!=', 77)
            ->where('categoryid', '!=', 80)
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

        // Fix for PUT requests with FormData - PHP doesn't populate $_POST or $_FILES for PUT
        // We need to manually parse multipart/form-data to extract both text fields and files
        if (
            $request->isMethod('put') &&
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
                $parsedFiles = [];

                Log::info('CategoryController: Parsing multipart/form-data', [
                    'boundary' => $boundary,
                    'parts_count' => count($parts),
                ]);

                foreach ($parts as $index => $part) {
                    $part = trim($part);
                    if (empty($part) || $part === '--') {
                        continue;
                    }

                    // Check if this is a file upload (has filename attribute)
                    if (preg_match('/Content-Disposition:\s*form-data;\s*name="([^"]+)";\s*filename="([^"]+)"(?:\s*\r?\nContent-Type:\s*([^\r\n]+))?\s*\r?\n\r?\n(.*)/s', $part, $fileMatches)) {
                        $fieldName = $fileMatches[1];
                        $fileName = $fileMatches[2];
                        $contentType = isset($fileMatches[3]) && !empty($fileMatches[3]) ? trim($fileMatches[3]) : 'application/octet-stream';
                        $fileContent = $fileMatches[4];

                        // Remove trailing boundary if present
                        $fileContent = preg_replace('/\r?\n--.*$/s', '', $fileContent);
                        $fileContent = rtrim($fileContent, "\r\n");

                        Log::info('CategoryController: Found file in multipart', [
                            'field_name' => $fieldName,
                            'file_name' => $fileName,
                            'content_type' => $contentType,
                            'content_size' => strlen($fileContent),
                        ]);

                        if (strlen($fileContent) > 0) {
                            // Create temporary file
                            $tempFile = tempnam(sys_get_temp_dir(), 'laravel_upload_');
                            file_put_contents($tempFile, $fileContent);

                            // Create UploadedFile instance
                            $uploadedFile = new UploadedFile(
                                $tempFile,
                                $fileName,
                                $contentType,
                                null,
                                true // test mode
                            );

                            $parsedFiles[$fieldName] = $uploadedFile;
                        }
                    }
                    // Check if this is a regular text field (no filename attribute)
                    elseif (preg_match('/Content-Disposition:\s*form-data;\s*name="([^"]+)"\s*\r?\n\r?\n(.*)/s', $part, $textMatches)) {
                        $fieldName = $textMatches[1];
                        $fieldValue = $textMatches[2];

                        // Remove trailing boundary if present
                        $fieldValue = preg_replace('/\r?\n--.*$/s', '', $fieldValue);
                        $fieldValue = trim($fieldValue, "\r\n");

                        if ($fieldName !== '_method') {
                            $parsedData[$fieldName] = $fieldValue;
                        }
                    }
                }

                // Merge parsed data
                if (!empty($parsedData)) {
                    $request->merge($parsedData);
                }

                // Add files to request
                if (!empty($parsedFiles)) {
                    Log::info('CategoryController: Adding parsed files to request', [
                        'files' => array_keys($parsedFiles),
                    ]);
                    foreach ($parsedFiles as $key => $file) {
                        $request->files->set($key, $file);
                    }
                }
            }
        }

        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'categoryAR' => 'required|string|max:255',
            'parentid' => 'nullable|integer',
            'metatitle' => 'nullable|string|max:500',
            'metatitleAR' => 'nullable|string|max:500',
            'metakeyword' => 'nullable|string|max:1000',
            'metakeywordAR' => 'nullable|string|max:1000',
            'metadescr' => 'nullable|string|max:1500',
            'metadescrAR' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'photo_mobile' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'ispublished' => 'nullable',
            'showmenu' => 'nullable',
            'displayorder' => 'nullable|integer',
        ], [
            'category.required' => 'Category name (EN) is required.',
            'categoryAR.required' => 'Category name (AR) is required.',
            'parentid.integer' => 'Parent category must be a valid selection.',
            'photo.image' => 'Desktop photo must be an image.',
            'photo.max' => 'Desktop photo must not exceed 10MB.',
            'photo_mobile.image' => 'Mobile photo must be an image.',
            'photo_mobile.max' => 'Mobile photo must not exceed 10MB.',
        ]);

        // For update: Only regenerate categorycode if category name changed
        if ($category->category !== $validated['category']) {
            // Generate categorycode from category name (EN)
            $categoryName = trim($validated['category']);
            $baseCode = strtolower($categoryName);

            // Remove Arabic and special characters, keep only alphanumeric and spaces
            $baseCode = preg_replace('/[^\p{L}\p{N}\s]+/u', '', $baseCode);

            // Replace spaces and multiple spaces with single hyphen
            $baseCode = preg_replace('/\s+/', '-', $baseCode);

            // Remove leading/trailing hyphens
            $baseCode = trim($baseCode, '-');

            // If still empty, use transliteration or fallback
            if (empty($baseCode)) {
                $baseCode = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $categoryName));
                $baseCode = trim($baseCode, '-');

                if (empty($baseCode)) {
                    $baseCode = 'category-' . time();
                }
            }

            // Ensure categorycode is unique (excluding current category)
            $categorycode = $baseCode;
            $counter = 1;
            while (Category::where('categorycode', $categorycode)->where('categoryid', '!=', $category->categoryid)->exists()) {
                $categorycode = $baseCode . '-' . $counter;
                $counter++;
            }

            $validated['categorycode'] = $categorycode;
        }
        // If category name didn't change, keep existing categorycode (don't include it in validated)

        // Set default parentid if not provided
        if (empty($validated['parentid'])) {
            $validated['parentid'] = 0;
        }

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

        // Handle file uploads using trait (old images auto-deleted)
        // Check both $request->hasFile() and $_FILES for PUT requests
        $hasPhoto = $request->hasFile('photo') || (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK);
        $hasPhotoMobile = $request->hasFile('photo_mobile') || (isset($_FILES['photo_mobile']) && $_FILES['photo_mobile']['error'] === UPLOAD_ERR_OK);

        Log::info('CategoryController: Starting file upload process', [
            'category_id' => $category->categoryid,
            'request_method' => $request->method(),
            'has_photo' => $hasPhoto,
            'has_photo_mobile' => $hasPhotoMobile,
            'request_hasFile_photo' => $request->hasFile('photo'),
            'request_hasFile_photo_mobile' => $request->hasFile('photo_mobile'),
            '_FILES_photo' => isset($_FILES['photo']) ? [
                'name' => $_FILES['photo']['name'] ?? null,
                'error' => $_FILES['photo']['error'] ?? null,
                'size' => $_FILES['photo']['size'] ?? null,
            ] : 'not set',
            '_FILES_photo_mobile' => isset($_FILES['photo_mobile']) ? [
                'name' => $_FILES['photo_mobile']['name'] ?? null,
                'error' => $_FILES['photo_mobile']['error'] ?? null,
                'size' => $_FILES['photo_mobile']['size'] ?? null,
            ] : 'not set',
            'old_photo' => $category->photo,
            'old_photo_mobile' => $category->photo_mobile,
        ]);

        // Handle file uploads - check all possible ways files might be accessible
        Log::info('CategoryController: Checking file access methods', [
            'all_files' => $request->allFiles(),
            'files_method' => $request->files->all(),
        ]);

        // Try to get files from request
        $photoFile = $request->file('photo');
        $photoMobileFile = $request->file('photo_mobile');

        // If not found, try accessing directly from files bag
        if (!$photoFile && $request->files->has('photo')) {
            $photoFile = $request->files->get('photo');
        }
        if (!$photoMobileFile && $request->files->has('photo_mobile')) {
            $photoMobileFile = $request->files->get('photo_mobile');
        }

        // If still not found and $_FILES is set, create UploadedFile manually
        if (!$photoFile && isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK && file_exists($_FILES['photo']['tmp_name'])) {
            $photoFile = \Illuminate\Http\UploadedFile::createFromBase(
                new \Symfony\Component\HttpFoundation\File\UploadedFile(
                    $_FILES['photo']['tmp_name'],
                    $_FILES['photo']['name'],
                    $_FILES['photo']['type'],
                    $_FILES['photo']['error'],
                    true
                )
            );
        }

        if (!$photoMobileFile && isset($_FILES['photo_mobile']) && $_FILES['photo_mobile']['error'] === UPLOAD_ERR_OK && file_exists($_FILES['photo_mobile']['tmp_name'])) {
            $photoMobileFile = \Illuminate\Http\UploadedFile::createFromBase(
                new \Symfony\Component\HttpFoundation\File\UploadedFile(
                    $_FILES['photo_mobile']['tmp_name'],
                    $_FILES['photo_mobile']['name'],
                    $_FILES['photo_mobile']['type'],
                    $_FILES['photo_mobile']['error'],
                    true
                )
            );
        }

        // Upload photo if found
        if ($photoFile && $photoFile->isValid()) {
            Log::info('CategoryController: Uploading photo', [
                'file_name' => $photoFile->getClientOriginalName(),
                'file_size' => $photoFile->getSize(),
                'mime_type' => $photoFile->getMimeType(),
            ]);
            $validated['photo'] = $this->uploadImage($photoFile, $category->photo, 'category');
            Log::info('CategoryController: Photo upload result', [
                'uploaded_filename' => $validated['photo'],
            ]);
        } else {
            Log::info('CategoryController: No valid photo file found', [
                'photoFile_exists' => $photoFile !== null,
                'photoFile_valid' => $photoFile ? $photoFile->isValid() : false,
            ]);
        }

        // Upload photo_mobile if found
        if ($photoMobileFile && $photoMobileFile->isValid()) {
            Log::info('CategoryController: Uploading photo_mobile', [
                'file_name' => $photoMobileFile->getClientOriginalName(),
                'file_size' => $photoMobileFile->getSize(),
                'mime_type' => $photoMobileFile->getMimeType(),
            ]);
            $validated['photo_mobile'] = $this->uploadImage($photoMobileFile, $category->photo_mobile, 'category');
            Log::info('CategoryController: Photo_mobile upload result', [
                'uploaded_filename' => $validated['photo_mobile'],
            ]);
        } else {
            Log::info('CategoryController: No valid photo_mobile file found', [
                'photoMobileFile_exists' => $photoMobileFile !== null,
                'photoMobileFile_valid' => $photoMobileFile ? $photoMobileFile->isValid() : false,
            ]);
        }

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

        // Delete associated images
        if ($category->photo) {
            $this->deleteImage($category->photo, 'category');
        }
        if ($category->photo_mobile) {
            $this->deleteImage($category->photo_mobile, 'category');
        }

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

    /**
     * Remove image from category (via AJAX with confirmation)
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeImage(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        $column = $request->input('column'); // 'photo' or 'photo_mobile'

        // Validate column name
        if (!in_array($column, ['photo', 'photo_mobile'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid column name'
            ], 400);
        }

        // Remove image using trait
        $this->removeImageFromModel($category, $column, 'category');

        return response()->json([
            'success' => true,
            'message' => 'Image removed successfully'
        ]);
    }
}