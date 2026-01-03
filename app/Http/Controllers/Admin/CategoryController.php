<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\UploadedFile;

class CategoryController extends Controller
{
    use ImageUploadTrait;
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
        $categories = $query->orderBy('categoryid', 'desc')->paginate($perPage);

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
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'photo_mobile' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
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
            'photo.max' => 'Desktop photo must not exceed 10MB.',
            'photo_mobile.image' => 'Mobile photo must be an image.',
            'photo_mobile.max' => 'Mobile photo must not exceed 10MB.',
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
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'photo_mobile' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
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
            'photo.max' => 'Desktop photo must not exceed 10MB.',
            'photo_mobile.image' => 'Mobile photo must be an image.',
            'photo_mobile.max' => 'Mobile photo must not exceed 10MB.',
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
