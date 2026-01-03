<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Occassion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\UploadedFile;

class OccassionController extends Controller
{
    use ImageUploadTrait;
    public function index(Request $request)
    {
        $query = Occassion::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('occassion', 'like', "%{$search}%")
                  ->orWhere('occassionAR', 'like', "%{$search}%")
                  ->orWhere('occassioncode', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 25);
        $occassions = $query->orderBy('occassionid', 'desc')->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.partials.occassions-table', compact('occassions'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $occassions])->render(),
            ]);
        }

        return view('admin.occassion.index', compact('occassions'));
    }

    public function create(Request $request)
    {
        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.occassion.partials.occassion-form', ['occassion' => null])->render(),
            ]);
        }

        return view('admin.occassion.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'occassion' => 'required|string|max:255',
            'occassionAR' => 'required|string|max:255',
            'occassioncode' => [
                'required',
                'string',
                'max:255',
                Rule::unique('occassion', 'occassioncode')
            ],
            'metatitle' => 'nullable|string|max:500',
            'metatitleAR' => 'nullable|string|max:500',
            'metakeyword' => 'nullable|string|max:1000',
            'metakeywordAR' => 'nullable|string|max:1000',
            'metadescr' => 'nullable|string|max:1500',
            'metadescrAR' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'photo_mobile' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'ispublished' => 'nullable',
            'showhome' => 'nullable',
        ], [
            'occassion.required' => 'Occasion name (EN) is required.',
            'occassionAR.required' => 'Occasion name (AR) is required.',
            'occassioncode.required' => 'Occasion code is required.',
            'occassioncode.unique' => 'This occasion code already exists. Please choose a different code.',
            'photo.image' => 'Photo must be an image.',
            'photo.max' => 'Photo must not exceed 10MB.',
            'photo_mobile.image' => 'Mobile photo must be an image.',
            'photo_mobile.max' => 'Mobile photo must not exceed 10MB.',
        ]);

        // Handle boolean fields
        $validated['ispublished'] = $request->has('ispublished') ? 1 : 0;
        $validated['showhome'] = $request->has('showhome') ? 1 : 0;
        $validated['updatedby'] = auth()->id() ?? 1;
        $validated['updateddate'] = now();

        // Handle file uploads using trait
        if ($request->hasFile('photo')) {
            $validated['photo'] = $this->uploadImage($request->file('photo'), null, 'occassion');
        }

        if ($request->hasFile('photo_mobile')) {
            $validated['photo_mobile'] = $this->uploadImage($request->file('photo_mobile'), null, 'occassion');
        }

        try {
            $occassion = Occassion::create($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Occasion created successfully',
                    'data' => $occassion
                ]);
            }

            return redirect()->route('admin.occassion')
                ->with('success', 'Occasion created successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database constraint violations
            $errorMessage = 'An error occurred while saving the occasion.';
            $errorField = 'occassioncode';

            if ($e->getCode() == 23000) {
                if (strpos($e->getMessage(), 'occassioncode') !== false) {
                    $errorMessage = 'This occasion code already exists. Please choose a different code.';
                    $errorField = 'occassioncode';
                } elseif (strpos($e->getMessage(), 'uniq-occassion') !== false) {
                    $errorMessage = 'This occasion name already exists. Please choose a different name.';
                    $errorField = 'occassion';
                } else {
                    $errorMessage = 'A database constraint violation occurred. Please check your input.';
                }
            } else {
                Log::error('Occasion creation error: ' . $e->getMessage());
                $errorMessage = 'An error occurred while saving the occasion: ' . $e->getMessage();
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
            Log::error('Occasion creation error: ' . $e->getMessage());

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
        $occassion = Occassion::findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.occassion.partials.occassion-view', compact('occassion'))->render(),
            ]);
        }

        return view('admin.occassion.show', compact('occassion'));
    }

    public function edit(Request $request, $id)
    {
        $occassion = Occassion::findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.occassion.partials.occassion-form', ['occassion' => $occassion])->render(),
            ]);
        }

        return view('admin.occassion.edit', compact('occassion'));
    }

    public function update(Request $request, $id)
    {
        $occassion = Occassion::findOrFail($id);

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
                    foreach ($parsedFiles as $key => $file) {
                        $request->files->set($key, $file);
                    }
                }
            }
        }

        $validated = $request->validate([
            'occassion' => 'required|string|max:255',
            'occassionAR' => 'required|string|max:255',
            'occassioncode' => [
                'required',
                'string',
                'max:255',
                Rule::unique('occassion', 'occassioncode')->ignore($occassion->occassionid, 'occassionid')
            ],
            'metatitle' => 'nullable|string|max:500',
            'metatitleAR' => 'nullable|string|max:500',
            'metakeyword' => 'nullable|string|max:1000',
            'metakeywordAR' => 'nullable|string|max:1000',
            'metadescr' => 'nullable|string|max:1500',
            'metadescrAR' => 'nullable|string|max:1000',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'photo_mobile' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:10240',
            'ispublished' => 'nullable',
            'showhome' => 'nullable',
        ], [
            'occassion.required' => 'Occasion name (EN) is required.',
            'occassionAR.required' => 'Occasion name (AR) is required.',
            'occassioncode.required' => 'Occasion code is required.',
            'occassioncode.unique' => 'This occasion code already exists. Please choose a different code.',
            'photo.image' => 'Photo must be an image.',
            'photo.max' => 'Photo must not exceed 10MB.',
            'photo_mobile.image' => 'Mobile photo must be an image.',
            'photo_mobile.max' => 'Mobile photo must not exceed 10MB.',
        ]);

        // Handle boolean fields
        $validated['ispublished'] = $request->has('ispublished') ? 1 : 0;
        $validated['showhome'] = $request->has('showhome') ? 1 : 0;
        $validated['updatedby'] = auth()->id() ?? 1;
        $validated['updateddate'] = now();

        // Handle file uploads - check all possible ways files might be accessible
        $photoFile = $request->file('photo');
        $photoMobileFile = $request->file('photo_mobile');

        // If not found, try accessing directly from files bag
        if (!$photoFile && $request->files->has('photo')) {
            $photoFile = $request->files->get('photo');
        }
        if (!$photoMobileFile && $request->files->has('photo_mobile')) {
            $photoMobileFile = $request->files->get('photo_mobile');
        }

        // Upload photo if found
        if ($photoFile && $photoFile->isValid()) {
            $validated['photo'] = $this->uploadImage($photoFile, $occassion->photo, 'occassion');
        }

        // Upload photo_mobile if found
        if ($photoMobileFile && $photoMobileFile->isValid()) {
            $validated['photo_mobile'] = $this->uploadImage($photoMobileFile, $occassion->photo_mobile, 'occassion');
        }

        try {
            $occassion->update($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Occasion updated successfully',
                    'data' => $occassion
                ]);
            }

            return redirect()->route('admin.occassion')
                ->with('success', 'Occasion updated successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database constraint violations
            $errorMessage = 'An error occurred while updating the occasion.';
            $errorField = 'occassioncode';

            if ($e->getCode() == 23000) {
                if (strpos($e->getMessage(), 'occassioncode') !== false) {
                    $errorMessage = 'This occasion code already exists. Please choose a different code.';
                    $errorField = 'occassioncode';
                } elseif (strpos($e->getMessage(), 'uniq-occassion') !== false) {
                    $errorMessage = 'This occasion name already exists. Please choose a different name.';
                    $errorField = 'occassion';
                } else {
                    $errorMessage = 'A database constraint violation occurred. Please check your input.';
                }
            } else {
                Log::error('Occasion update error: ' . $e->getMessage());
                $errorMessage = 'An error occurred while updating the occasion: ' . $e->getMessage();
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
            Log::error('Occasion update error: ' . $e->getMessage());

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
        $occassion = Occassion::findOrFail($id);

        try {
            // Delete photos if they exist
            if ($occassion->photo) {
                $this->deleteImage($occassion->photo, 'occassion');
            }
            if ($occassion->photo_mobile) {
                $this->deleteImage($occassion->photo_mobile, 'occassion');
            }

            $occassion->delete();

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Occasion deleted successfully'
                ]);
            }

            return redirect()->route('admin.occassion')
                ->with('success', 'Occasion deleted successfully');
        } catch (\Exception $e) {
            Log::error('Occasion deletion error: ' . $e->getMessage());

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while deleting the occasion.'
                ], 422);
            }

            return back()->withErrors(['error' => 'An error occurred while deleting the occasion.']);
        }
    }

    /**
     * Remove image from occasion (via AJAX with confirmation)
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeImage(Request $request, $id)
    {
        $occassion = Occassion::findOrFail($id);
        $column = $request->input('column'); // 'photo' or 'photo_mobile'

        // Validate column name
        if (!in_array($column, ['photo', 'photo_mobile'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid column name'
            ], 400);
        }

        // Remove image using trait
        $this->removeImageFromModel($occassion, $column, 'occassion');

        return response()->json([
            'success' => true,
            'message' => 'Image removed successfully'
        ]);
    }
}
