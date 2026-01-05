<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Exception;

class SizeController extends Controller
{
    public function index(Request $request)
    {
        // Check if this is a DataTables request
        if ($request->has('draw')) {
            return $this->getDataTablesData($request);
        }

        // Load initial view (for non-DataTables requests)
        return view('admin.masters.sizes');
    }

    private function getDataTablesData(Request $request)
    {
        try {
            $query = Size::sizes(); // Use scope to filter by fkfilterid = 3
            $filteredCountQuery = Size::sizes();

            // Get total records count
            $totalRecords = Size::sizes()->count();

            // DataTables search (global search)
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

            // Get filtered count after search
            $filteredAfterSearch = $filteredCountQuery->count();

            // Ordering
            $orderColumnIndex = $request->input('order.0.column', 0);
            $orderDir = $request->input('order.0.dir', 'asc');

            $columns = [
                'filtervalue',
                'filtervalueAR',
                'displayorder',
                'filtervalueid' // For action column
            ];

            $orderColumn = $columns[$orderColumnIndex] ?? 'displayorder';
            $query->orderBy($orderColumn, $orderDir);

            // Pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 25);
            $sizes = $query->skip($start)->take($length)->get();

            // Format data for DataTables
            $data = [];
            $sizeBaseUrl = url('/admin/sizes');
            foreach ($sizes as $size) {
                $data[] = [
                    'filtervalue' => $size->filtervalue ?? '',
                    'filtervalueAR' => $size->filtervalueAR ?? 'N/A',
                    'displayorder' => $size->displayorder ?? 0,
                    'action' => $size->filtervalueid // For action buttons
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredAfterSearch,
                'data' => $data
            ]);
        } catch (Exception $e) {
            Log::error('Size DataTables Error: ' . $e->getMessage());
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
