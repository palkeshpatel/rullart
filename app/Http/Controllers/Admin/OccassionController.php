<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Occassion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Traits\ImageUploadTrait;
use Illuminate\Http\UploadedFile;
use Exception;

class OccassionController extends Controller
{
    use ImageUploadTrait;
    public function index(Request $request)
    {
        // Check if this is a DataTables request
        if ($request->has('draw')) {
            return $this->getDataTablesData($request);
        }

        // Return view for initial page load
        return view('admin.occassion.index');
    }

    /**
     * Get DataTables data for server-side processing
     */
    private function getDataTablesData(Request $request)
    {
        try {
            // Base query for counting
            $countQuery = Occassion::query();
            $totalRecords = $countQuery->count();

            // Build base query for data
            $query = Occassion::query();
            $filteredCountQuery = Occassion::query();

            // DataTables search (global search)
            $searchValue = $request->input('search.value', '');
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('occassion', 'like', "%{$searchValue}%")
                        ->orWhere('occassionAR', 'like', "%{$searchValue}%");
                });

                $filteredCountQuery->where(function ($q) use ($searchValue) {
                    $q->where('occassion', 'like', "%{$searchValue}%")
                        ->orWhere('occassionAR', 'like', "%{$searchValue}%");
                });
            }

            $filteredAfterSearch = $filteredCountQuery->count();

            // Ordering
            $orderColumnIndex = $request->input('order.0.column', 0);
            $orderDir = $request->input('order.0.dir', 'desc');
            $columns = ['occassion', 'photo', 'ispublished', 'updateddate', 'occassionid'];
            $orderColumn = $columns[$orderColumnIndex] ?? 'occassionid';
            $query->orderBy($orderColumn, $orderDir);

            // Pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 25);
            $occassions = $query->skip($start)->take($length)->get();

            // Format data for DataTables
            $data = [];
            foreach ($occassions as $occassion) {
                $photoUrl = $occassion->photo ? asset('storage/' . $occassion->photo) : null;
                $data[] = [
                    'occassion' => $occassion->occassion ?? '',
                    'photo' => $photoUrl ? '<img src="' . $photoUrl . '" alt="Photo" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">' : '(No photo)',
                    'ispublished' => $occassion->ispublished ? 'Yes' : 'No',
                    'updateddate' => $occassion->updateddate ? \Carbon\Carbon::parse($occassion->updateddate)->format('d/M/Y') : 'N/A',
                    'action' => $occassion->occassionid
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredAfterSearch,
                'data' => $data
            ]);
        } catch (Exception $e) {
            Log::error('Occasion DataTables Error: ' . $e->getMessage());
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
            'photo.image' => 'Photo must be an image.',
            'photo.max' => 'Photo must not exceed 10MB.',
            'photo_mobile.image' => 'Mobile photo must be an image.',
            'photo_mobile.max' => 'Mobile photo must not exceed 10MB.',
        ]);

        // Generate occasioncode from occasion name (EN)
        $occasionName = trim($validated['occassion']);
        $baseCode = strtolower($occasionName);
        
        // Remove Arabic and special characters, keep only alphanumeric and spaces
        $baseCode = preg_replace('/[^\p{L}\p{N}\s]+/u', '', $baseCode);
        
        // Replace spaces and multiple spaces with single hyphen
        $baseCode = preg_replace('/\s+/', '-', $baseCode);
        
        // Remove leading/trailing hyphens
        $baseCode = trim($baseCode, '-');
        
        // If still empty, use transliteration or fallback
        if (empty($baseCode)) {
            $baseCode = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $occasionName));
            $baseCode = trim($baseCode, '-');
            
            if (empty($baseCode)) {
                $baseCode = 'occasion-' . time();
            }
        }
        
        // Ensure occasioncode is unique
        $occasioncode = $baseCode;
        $counter = 1;
        while (Occassion::where('occassioncode', $occasioncode)->exists()) {
            $occasioncode = $baseCode . '-' . $counter;
            $counter++;
        }
        
        $validated['occassioncode'] = $occasioncode;

        // Handle boolean fields
        $validated['ispublished'] = $request->has('ispublished') ? 1 : 0;
        $validated['showhome'] = $request->has('showhome') ? 1 : 0;
        $validated['updatedby'] = auth()->id() ?? 1;
        $validated['updateddate'] = now();

        // Handle file uploads using trait
        // photo field is NOT NULL in database, so always set it (empty string if no file)
        if ($request->hasFile('photo')) {
            $validated['photo'] = $this->uploadImage($request->file('photo'), null, 'occassion');
        } else {
            $validated['photo'] = ''; // Set empty string if no photo uploaded (matching CI project)
        }

        // photo_mobile can be NULL, so only set if file is uploaded
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
            'photo.image' => 'Photo must be an image.',
            'photo.max' => 'Photo must not exceed 10MB.',
            'photo_mobile.image' => 'Mobile photo must be an image.',
            'photo_mobile.max' => 'Mobile photo must not exceed 10MB.',
        ]);

        // For update: Only regenerate occasioncode if occasion name changed
        if ($occassion->occassion !== $validated['occassion']) {
            // Generate occasioncode from occasion name (EN)
            $occasionName = trim($validated['occassion']);
            $baseCode = strtolower($occasionName);
            
            // Remove Arabic and special characters, keep only alphanumeric and spaces
            $baseCode = preg_replace('/[^\p{L}\p{N}\s]+/u', '', $baseCode);
            
            // Replace spaces and multiple spaces with single hyphen
            $baseCode = preg_replace('/\s+/', '-', $baseCode);
            
            // Remove leading/trailing hyphens
            $baseCode = trim($baseCode, '-');
            
            // If still empty, use transliteration or fallback
            if (empty($baseCode)) {
                $baseCode = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $occasionName));
                $baseCode = trim($baseCode, '-');
                
                if (empty($baseCode)) {
                    $baseCode = 'occasion-' . time();
                }
            }
            
            // Ensure occasioncode is unique (excluding current occasion)
            $occasioncode = $baseCode;
            $counter = 1;
            while (Occassion::where('occassioncode', $occasioncode)->where('occassionid', '!=', $occassion->occassionid)->exists()) {
                $occasioncode = $baseCode . '-' . $counter;
                $counter++;
            }
            
            $validated['occassioncode'] = $occasioncode;
        }
        // If occasion name didn't change, keep existing occasioncode

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
        // photo field is NOT NULL in database, so always set it (empty string if no file)
        if ($photoFile && $photoFile->isValid()) {
            $validated['photo'] = $this->uploadImage($photoFile, $occassion->photo, 'occassion');
        } else {
            // If no new photo uploaded, keep existing photo or set empty string
            $validated['photo'] = $occassion->photo ?? '';
        }

        // Upload photo_mobile if found
        // photo_mobile can be NULL, so only update if file is uploaded
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
