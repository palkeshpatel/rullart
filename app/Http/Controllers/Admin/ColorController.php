<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Exception;

class ColorController extends Controller
{
    public function index(Request $request)
    {
        // Check if this is a DataTables request
        if ($request->has('draw')) {
            return $this->getDataTablesData($request);
        }

        // Return view for initial page load
        return view('admin.masters.colors');
    }

    /**
     * Get DataTables data for server-side processing
     */
    private function getDataTablesData(Request $request)
    {
        try {
            $countQuery = Color::colors();
            $totalRecords = $countQuery->count();

            $query = Color::colors();
            $filteredCountQuery = Color::colors();

            // DataTables search
            $searchValue = $request->input('search.value', '');
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('filtervalue', 'like', "%{$searchValue}%")
                        ->orWhere('filtervalueAR', 'like', "%{$searchValue}%");
                });

                $filteredCountQuery->where(function ($q) use ($searchValue) {
                    $q->where('filtervalue', 'like', "%{$searchValue}%")
                        ->orWhere('filtervalueAR', 'like', "%{$searchValue}%");
                });
            }

            $filteredAfterSearch = $filteredCountQuery->count();

            // Ordering
            $orderColumnIndex = $request->input('order.0.column', 0);
            $orderDir = $request->input('order.0.dir', 'asc');
            $columns = ['filtervalue', 'filtervalueAR', 'filtervalueid'];
            $orderColumn = $columns[$orderColumnIndex] ?? 'filtervalue';
            $query->orderBy($orderColumn, $orderDir);

            // Pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 25);
            $colors = $query->skip($start)->take($length)->get();

            // Format data
            $data = [];
            foreach ($colors as $color) {
                $data[] = [
                    'filtervalue' => $color->filtervalue ?? '',
                    'filtervalueAR' => $color->filtervalueAR ?? 'N/A',
                    'action' => $color->filtervalueid
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredAfterSearch,
                'data' => $data
            ]);
        } catch (Exception $e) {
            Log::error('Color DataTables Error: ' . $e->getMessage());
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
                'html' => view('admin.masters.partials.color.color-form', ['color' => null])->render(),
            ]);
        }

        return view('admin.masters.color-create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'filtervalue' => [
                'required',
                'string',
                'max:255',
                Rule::unique('filtervalues', 'filtervalue')->where(function ($query) {
                    return $query->where('fkfilterid', 2);
                })
            ],
            'filtervalueAR' => 'nullable|string|max:255',
            'filtervaluecode' => 'nullable|string|max:255',
            'isactive' => 'nullable',
            'displayorder' => 'nullable|integer',
        ], [
            'filtervalue.unique' => 'This color name already exists. Please choose a different name.',
            'filtervalue.required' => 'Color name (EN) is required.',
        ]);

        $validated['fkfilterid'] = 2; // Color filter ID
        $validated['isactive'] = $request->has('isactive') ? 1 : 0;
        $validated['filtervaluecode'] = $validated['filtervaluecode'] ?? '';
        $validated['displayorder'] = $validated['displayorder'] ?? 0;

        try {
            $color = Color::create($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Color created successfully',
                    'data' => $color
                ]);
            }

            return redirect()->route('admin.colors')
                ->with('success', 'Color created successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database constraint violations
            if ($e->getCode() == 23000) {
                $errorMessage = 'This color name already exists. Please choose a different name.';
            } else {
                $errorMessage = 'An error occurred while saving the color.';
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
        $color = Color::colors()->findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.color.color-view', compact('color'))->render(),
            ]);
        }

        return view('admin.masters.color-view', compact('color'));
    }

    public function edit(Request $request, $id)
    {
        $color = Color::colors()->findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.color.color-form', compact('color'))->render(),
            ]);
        }

        return view('admin.masters.color-edit', compact('color'));
    }

    public function update(Request $request, $id)
    {
        $color = Color::colors()->findOrFail($id);

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
                    return $query->where('fkfilterid', 2);
                })->ignore($color->getKey(), $color->getKeyName())
            ],
            'filtervalueAR' => 'nullable|string|max:255',
            'filtervaluecode' => 'nullable|string|max:255',
            'isactive' => 'nullable',
            'displayorder' => 'nullable|integer',
        ], [
            'filtervalue.unique' => 'This color name already exists. Please choose a different name.',
            'filtervalue.required' => 'Color name (EN) is required.',
        ]);

        $validated['isactive'] = $request->has('isactive') ? 1 : 0;
        $validated['displayorder'] = $validated['displayorder'] ?? 0;

        try {
            $color->update($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Color updated successfully',
                    'data' => $color
                ]);
            }

            return redirect()->route('admin.colors')
                ->with('success', 'Color updated successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database constraint violations
            if ($e->getCode() == 23000) {
                $errorMessage = 'This color name already exists. Please choose a different name.';
            } else {
                $errorMessage = 'An error occurred while updating the color.';
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
        $color = Color::colors()->findOrFail($id);
        $color->delete();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Color deleted successfully'
            ]);
        }

        return redirect()->route('admin.colors')
            ->with('success', 'Color deleted successfully');
    }
}
