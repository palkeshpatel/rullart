<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class DiscountController extends Controller
{
    public function index(Request $request)
    {
        $query = Discount::query();

        // Filter by active status
        if ($request->has('active') && $request->active !== '') {
            $query->where('isactive', $request->active);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->where('startdate', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->where('enddate', '<=', $request->end_date);
        }

        // Sorting
        $sortColumn = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        $perPage = $request->get('per_page', 25);
        $discounts = $query->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.discounts.discounts-table', compact('discounts'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $discounts])->render(),
            ]);
        }

        return view('admin.masters.discounts', compact('discounts'));
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
            $discount = Discount::create($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Discount created successfully',
                    'data' => $discount
                ]);
            }

            return redirect()->route('admin.discounts')
                ->with('success', 'Discount created successfully');
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
