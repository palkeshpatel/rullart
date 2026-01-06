<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Exception;

class DiscountController extends Controller
{
    public function index(Request $request)
    {
        // Get the first (or only) discount record, or null if none exists
        $discount = Discount::orderBy('id', 'desc')->first();
        
        // Return view with discount data (or null if no discount exists)
        return view('admin.masters.discounts', compact('discount'));
    }

    /**
     * Get DataTables data for server-side processing
     */
    private function getDataTablesData(Request $request)
    {
        try {
            $countQuery = Discount::query();
            $totalRecords = $countQuery->count();

            $query = Discount::query();
            $filteredCountQuery = Discount::query();

            // Filter by active status
            if ($request->has('active') && $request->active !== '') {
                $query->where('isactive', $request->active);
                $filteredCountQuery->where('isactive', $request->active);
            }

            // DataTables search
            $searchValue = $request->input('search.value', '');
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('rate', 'like', "%{$searchValue}%");
                });
                $filteredCountQuery->where(function ($q) use ($searchValue) {
                    $q->where('rate', 'like', "%{$searchValue}%");
                });
            }

            $filteredAfterSearch = $filteredCountQuery->count();

            // Ordering
            $orderColumnIndex = $request->input('order.0.column', 0);
            $orderDir = $request->input('order.0.dir', 'desc');
            $columns = ['id', 'rate', 'startdate', 'enddate', 'days', 'isactive', 'id'];
            $orderColumn = $columns[$orderColumnIndex] ?? 'id';
            $query->orderBy($orderColumn, $orderDir);

            // Pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 25);
            $discounts = $query->skip($start)->take($length)->get();

            // Format data
            $data = [];
            foreach ($discounts as $discount) {
                $data[] = [
                    'id' => $discount->id ?? '',
                    'rate' => ($discount->rate ?? '') . '%',
                    'startdate' => $discount->startdate ? \Carbon\Carbon::parse($discount->startdate)->format('d-M-Y') : 'N/A',
                    'enddate' => $discount->enddate ? \Carbon\Carbon::parse($discount->enddate)->format('d-M-Y') : 'N/A',
                    'days' => $discount->days ?? 'N/A',
                    'isactive' => $discount->isactive ? 'Yes' : 'No',
                    'action' => $discount->id
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredAfterSearch,
                'data' => $data
            ]);
        } catch (Exception $e) {
            Log::error('Discount DataTables Error: ' . $e->getMessage());
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
                'html' => view('admin.masters.partials.discounts.discount-form', ['discount' => null])->render(),
            ]);
        }

        return view('admin.masters.discount-create');
    }

    public function store(Request $request)
    {
        // Check if discount already exists - if so, update it instead of creating new
        $existingDiscount = Discount::orderBy('id', 'desc')->first();
        
        $validated = $request->validate([
            'rate' => 'required|numeric|min:0|max:100',
            'enddate' => 'nullable|date',
            'isactive' => 'nullable',
        ], [
            'rate.required' => 'Discount Percentage is required.',
            'rate.max' => 'Discount Percentage cannot exceed 100%.',
        ]);

        $validated['isactive'] = $request->has('isactive') ? 1 : 0;

        try {
            if ($existingDiscount) {
                // Update existing discount
                $existingDiscount->update($validated);
                $discount = $existingDiscount;
                $message = 'Discount updated successfully';
            } else {
                // Create new discount
                $discount = Discount::create($validated);
                $message = 'Discount created successfully';
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => $discount
                ]);
            }

            return redirect()->route('admin.discounts')
                ->with('success', $message);
        } catch (\Illuminate\Database\QueryException $e) {
            $errorMessage = 'An error occurred while saving the discount.';

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['rate' => [$errorMessage]]
                ], 422);
            }

            return back()->withErrors(['rate' => $errorMessage])->withInput();
        }
    }

    public function show(Request $request, $id)
    {
        $discount = Discount::findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.discounts.discount-view', compact('discount'))->render(),
            ]);
        }

        return view('admin.masters.discount-view', compact('discount'));
    }

    public function edit(Request $request, $id)
    {
        $discount = Discount::findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.discounts.discount-form', compact('discount'))->render(),
            ]);
        }

        return view('admin.masters.discount-edit', compact('discount'));
    }

    public function update(Request $request, $id)
    {
        $discount = Discount::findOrFail($id);

        // Fix for PUT requests with FormData
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
            'rate' => 'required|numeric|min:0|max:100',
            'startdate' => 'nullable|date',
            'enddate' => 'nullable|date|after_or_equal:startdate',
            'days' => 'nullable|integer|min:0',
            'isactive' => 'nullable',
        ], [
            'rate.required' => 'Discount Rate is required.',
            'rate.max' => 'Discount Rate cannot exceed 100%.',
        ]);

        $validated['isactive'] = $request->has('isactive') ? 1 : 0;

        try {
            $discount->update($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Discount updated successfully',
                    'data' => $discount
                ]);
            }

            return redirect()->route('admin.discounts')
                ->with('success', 'Discount updated successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            $errorMessage = 'An error occurred while updating the discount.';

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['rate' => [$errorMessage]]
                ], 422);
            }

            return back()->withErrors(['rate' => $errorMessage])->withInput();
        }
    }

    public function destroy(Request $request, $id)
    {
        $discount = Discount::findOrFail($id);
        $discount->delete();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Discount deleted successfully'
            ]);
        }

        return redirect()->route('admin.discounts')
            ->with('success', 'Discount deleted successfully');
    }
}
