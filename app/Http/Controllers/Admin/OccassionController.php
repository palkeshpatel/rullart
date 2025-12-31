<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Occassion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OccassionController extends Controller
{
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
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'photo_mobile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'ispublished' => 'nullable',
            'showhome' => 'nullable',
        ], [
            'occassion.required' => 'Occasion name (EN) is required.',
            'occassionAR.required' => 'Occasion name (AR) is required.',
            'occassioncode.required' => 'Occasion code is required.',
            'occassioncode.unique' => 'This occasion code already exists. Please choose a different code.',
            'photo.image' => 'Photo must be an image.',
            'photo.max' => 'Photo must not exceed 5MB.',
            'photo_mobile.image' => 'Mobile photo must be an image.',
            'photo_mobile.max' => 'Mobile photo must not exceed 5MB.',
        ]);

        // Handle boolean fields
        $validated['ispublished'] = $request->has('ispublished') ? 1 : 0;
        $validated['showhome'] = $request->has('showhome') ? 1 : 0;
        $validated['updatedby'] = auth()->id() ?? 1;
        $validated['updateddate'] = now();

        // Create uploads directory if it doesn't exist
        $uploadPath = public_path('uploads/occassion');
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
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'photo_mobile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'ispublished' => 'nullable',
            'showhome' => 'nullable',
        ], [
            'occassion.required' => 'Occasion name (EN) is required.',
            'occassionAR.required' => 'Occasion name (AR) is required.',
            'occassioncode.required' => 'Occasion code is required.',
            'occassioncode.unique' => 'This occasion code already exists. Please choose a different code.',
            'photo.image' => 'Photo must be an image.',
            'photo.max' => 'Photo must not exceed 5MB.',
            'photo_mobile.image' => 'Mobile photo must be an image.',
            'photo_mobile.max' => 'Mobile photo must not exceed 5MB.',
        ]);

        // Handle boolean fields
        $validated['ispublished'] = $request->has('ispublished') ? 1 : 0;
        $validated['showhome'] = $request->has('showhome') ? 1 : 0;
        $validated['updatedby'] = auth()->id() ?? 1;
        $validated['updateddate'] = now();

        // Create uploads directory if it doesn't exist
        $uploadPath = public_path('uploads/occassion');
        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Handle file uploads (only if new files are uploaded)
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($occassion->photo && file_exists(public_path('uploads/occassion/' . $occassion->photo))) {
                unlink(public_path('uploads/occassion/' . $occassion->photo));
            }
            $photo = $request->file('photo');
            $photoName = time() . '_' . $photo->getClientOriginalName();
            $photo->move($uploadPath, $photoName);
            $validated['photo'] = $photoName;
        }

        if ($request->hasFile('photo_mobile')) {
            // Delete old photo if exists
            if ($occassion->photo_mobile && file_exists(public_path('uploads/occassion/' . $occassion->photo_mobile))) {
                unlink(public_path('uploads/occassion/' . $occassion->photo_mobile));
            }
            $photoMobile = $request->file('photo_mobile');
            $photoMobileName = time() . '_mobile_' . $photoMobile->getClientOriginalName();
            $photoMobile->move($uploadPath, $photoMobileName);
            $validated['photo_mobile'] = $photoMobileName;
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
            if ($occassion->photo && file_exists(public_path('uploads/occassion/' . $occassion->photo))) {
                unlink(public_path('uploads/occassion/' . $occassion->photo));
            }
            if ($occassion->photo_mobile && file_exists(public_path('uploads/occassion/' . $occassion->photo_mobile))) {
                unlink(public_path('uploads/occassion/' . $occassion->photo_mobile));
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
}
