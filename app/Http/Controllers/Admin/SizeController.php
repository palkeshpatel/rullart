<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SizeController extends Controller
{
    public function index(Request $request)
    {
        $query = Size::sizes(); // Use scope to filter by fkfilterid = 3

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('filtervalue', 'like', "%{$search}%")
                  ->orWhere('filtervalueAR', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortColumn = $request->get('sort', 'displayorder');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortColumn, $sortDirection);

        $perPage = $request->get('per_page', 25);
        $sizes = $query->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.sizes.sizes-table', compact('sizes'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $sizes])->render(),
            ]);
        }

        return view('admin.masters.sizes', compact('sizes'));
    }

    public function create(Request $request)
    {
        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.sizes.size-form', ['size' => null])->render(),
            ]);
        }

        return view('admin.masters.size-create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'filtervalue' => [
                'required',
                'string',
                'max:255',
                Rule::unique('filtervalues', 'filtervalue')->where(function ($query) {
                    return $query->where('fkfilterid', 3);
                })
            ],
            'filtervalueAR' => 'nullable|string|max:255',
            'filtervaluecode' => 'nullable|string|max:255',
            'isactive' => 'nullable',
            'displayorder' => 'nullable|integer',
        ], [
            'filtervalue.unique' => 'This size name already exists. Please choose a different name.',
            'filtervalue.required' => 'Size Name(EN) is required.',
        ]);

        $validated['fkfilterid'] = 3; // Size filter ID
        $validated['isactive'] = $request->has('isactive') ? 1 : 0;
        $validated['filtervaluecode'] = $validated['filtervaluecode'] ?? '';
        $validated['displayorder'] = $validated['displayorder'] ?? 0;

        try {
            $size = Size::create($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Size created successfully',
                    'data' => $size
                ]);
            }

            return redirect()->route('admin.sizes')
                ->with('success', 'Size created successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database constraint violations
            if ($e->getCode() == 23000) {
                $errorMessage = 'This size name already exists. Please choose a different name.';
            } else {
                $errorMessage = 'An error occurred while saving the size.';
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['filtervalue' => [$errorMessage]]
                ], 422);
            }

            return back()->withErrors(['filtervalue' => $errorMessage])->withInput();
        }
    }

    public function show(Request $request, $id)
    {
        $size = Size::sizes()->findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.sizes.size-view', compact('size'))->render(),
            ]);
        }

        return view('admin.masters.size-view', compact('size'));
    }

    public function edit(Request $request, $id)
    {
        $size = Size::sizes()->findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.sizes.size-form', compact('size'))->render(),
            ]);
        }

        return view('admin.masters.size-edit', compact('size'));
    }

    public function update(Request $request, $id)
    {
        $size = Size::sizes()->findOrFail($id);

        // Fix for PUT requests with FormData - PHP doesn't populate $_POST for PUT
        // Laravel can't read FormData from PUT requests automatically, so we need to parse it manually
        if (
            $request->isMethod('put') && empty($request->all()) &&
            $request->header('Content-Type') &&
            str_contains($request->header('Content-Type'), 'multipart/form-data')
        ) {

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
            'filtervalue' => [
                'required',
                'string',
                'max:255',
                Rule::unique('filtervalues', 'filtervalue')->where(function ($query) {
                    return $query->where('fkfilterid', 3);
                })->ignore($size->getKey(), $size->getKeyName())
            ],
            'filtervalueAR' => 'nullable|string|max:255',
            'filtervaluecode' => 'nullable|string|max:255',
            'isactive' => 'nullable',
            'displayorder' => 'nullable|integer',
        ], [
            'filtervalue.unique' => 'This size name already exists. Please choose a different name.',
            'filtervalue.required' => 'Size Name(EN) is required.',
        ]);

        $validated['isactive'] = $request->has('isactive') ? 1 : 0;
        $validated['displayorder'] = $validated['displayorder'] ?? 0;

        try {
            $size->update($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Size updated successfully',
                    'data' => $size
                ]);
            }

            return redirect()->route('admin.sizes')
                ->with('success', 'Size updated successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database constraint violations
            if ($e->getCode() == 23000) {
                $errorMessage = 'This size name already exists. Please choose a different name.';
            } else {
                $errorMessage = 'An error occurred while updating the size.';
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['filtervalue' => [$errorMessage]]
                ], 422);
            }

            return back()->withErrors(['filtervalue' => $errorMessage])->withInput();
        }
    }

    public function destroy(Request $request, $id)
    {
        $size = Size::sizes()->findOrFail($id);
        $size->delete();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Size deleted successfully'
            ]);
        }

        return redirect()->route('admin.sizes')
            ->with('success', 'Size deleted successfully');
    }
}
