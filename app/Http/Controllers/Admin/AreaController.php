<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class AreaController extends Controller
{
    public function index(Request $request)
    {
        $query = Area::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('areaname', 'like', "%{$search}%")
                  ->orWhere('areanameAR', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortColumn = $request->get('sort', 'areaid');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortColumn, $sortDirection);

        $perPage = $request->get('per_page', 25);
        $areas = $query->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.area.areas-table', compact('areas'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $areas])->render(),
            ]);
        }

        return view('admin.masters.areas', compact('areas'));
    }

    public function create(Request $request)
    {
        $countries = Country::where('isactive', 1)->orderBy('countryname')->get();

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.area.area-form', ['area' => null, 'countries' => $countries])->render(),
            ]);
        }

        return view('admin.masters.area-create', compact('countries'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fkcountryid' => 'required|integer|exists:countrymaster,countryid',
            'areaname' => [
                'required',
                'string',
                'max:255',
                Rule::unique('areamaster', 'areaname')->where(function ($query) use ($request) {
                    return $query->where('fkcountryid', $request->fkcountryid);
                })
            ],
            'areanameAR' => 'nullable|string|max:255',
            'isactive' => 'nullable',
        ], [
            'areaname.unique' => 'This area name already exists for the selected country. Please choose a different name.',
            'areaname.required' => 'Area Name(EN) is required.',
            'fkcountryid.required' => 'Country is required.',
        ]);

        $validated['isactive'] = $request->has('isactive') ? 1 : 0;

        try {
            $area = Area::create($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Area created successfully',
                    'data' => $area
                ]);
            }

            return redirect()->route('admin.areas')
                ->with('success', 'Area created successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database constraint violations
            if ($e->getCode() == 23000) {
                $errorMessage = 'This area name already exists for the selected country. Please choose a different name.';
            } else {
                $errorMessage = 'An error occurred while saving the area.';
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['areaname' => [$errorMessage]]
                ], 422);
            }

            return back()->withErrors(['areaname' => $errorMessage])->withInput();
        }
    }

    public function show(Request $request, $id)
    {
        $area = Area::with('country')->findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.area.area-view', compact('area'))->render(),
            ]);
        }

        return view('admin.masters.area-view', compact('area'));
    }

    public function edit(Request $request, $id)
    {
        $area = Area::findOrFail($id);
        $countries = Country::where('isactive', 1)->orderBy('countryname')->get();

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.area.area-form', compact('area', 'countries'))->render(),
            ]);
        }

        return view('admin.masters.area-edit', compact('area', 'countries'));
    }

    public function update(Request $request, $id)
    {
        $area = Area::findOrFail($id);

        // Fix for PUT requests with FormData - PHP doesn't populate $_POST for PUT
        // Laravel can't read FormData from PUT requests automatically, so we need to parse it manually
        if ($request->isMethod('put') && empty($request->all()) && 
            $request->header('Content-Type') && 
            str_contains($request->header('Content-Type'), 'multipart/form-data')) {
            
            // Parse multipart/form-data manually
            $content = $request->getContent();
            $boundary = null;
            
            // Extract boundary from Content-Type header
            if (preg_match('/boundary=([^;]+)/', $request->header('Content-Type'), $matches)) {
                $boundary = '--' . trim($matches[1]);
            }
            
            if ($boundary && $content) {
                $parts = explode($boundary, $content);
                $parsedData = [];
                
                foreach ($parts as $part) {
                    // Match field name and value in multipart format
                    if (preg_match('/Content-Disposition:\s*form-data;\s*name="([^"]+)"\s*\r?\n\r?\n(.*?)(?=\r?\n--|$)/s', $part, $matches)) {
                        $fieldName = $matches[1];
                        $fieldValue = trim($matches[2], "\r\n");
                        
                        // Skip _method (it's handled by Laravel's method spoofing)
                        if ($fieldName !== '_method') {
                            $parsedData[$fieldName] = $fieldValue;
                        }
                    }
                }
                
                // Merge parsed data into request so validation can access it
                if (!empty($parsedData)) {
                    $request->merge($parsedData);
                }
            }
        }

        $validated = $request->validate([
            'fkcountryid' => 'required|integer|exists:countrymaster,countryid',
            'areaname' => [
                'required',
                'string',
                'max:255',
                Rule::unique('areamaster', 'areaname')->where(function ($query) use ($request) {
                    return $query->where('fkcountryid', $request->fkcountryid);
                })->ignore($area->getKey(), $area->getKeyName())
            ],
            'areanameAR' => 'nullable|string|max:255',
            'isactive' => 'nullable',
        ], [
            'areaname.unique' => 'This area name already exists for the selected country. Please choose a different name.',
            'areaname.required' => 'Area Name(EN) is required.',
            'fkcountryid.required' => 'Country is required.',
        ]);

        $validated['isactive'] = $request->has('isactive') ? 1 : 0;

        try {
            $area->update($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Area updated successfully',
                    'data' => $area
                ]);
            }

            return redirect()->route('admin.areas')
                ->with('success', 'Area updated successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database constraint violations
            if ($e->getCode() == 23000) {
                $errorMessage = 'This area name already exists for the selected country. Please choose a different name.';
            } else {
                $errorMessage = 'An error occurred while updating the area.';
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['areaname' => [$errorMessage]]
                ], 422);
            }

            return back()->withErrors(['areaname' => $errorMessage])->withInput();
        }
    }

    public function destroy(Request $request, $id)
    {
        $area = Area::findOrFail($id);
        $area->delete();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Area deleted successfully'
            ]);
        }

        return redirect()->route('admin.areas')
            ->with('success', 'Area deleted successfully');
    }
}
