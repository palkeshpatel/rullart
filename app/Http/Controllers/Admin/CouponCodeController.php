<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CouponCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CouponCodeController extends Controller
{
    public function index(Request $request)
    {
        $query = CouponCode::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('couponcode', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->has('active') && $request->active !== '') {
            $query->where('isactive', $request->active);
        }

        // Sorting
        $sortColumn = $request->get('sort', 'couponcodeid');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        $perPage = $request->get('per_page', 25);
        $couponCodes = $query->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.coupon.coupon-code-table', compact('couponCodes'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $couponCodes])->render(),
            ]);
        }

        return view('admin.masters.coupon-code', compact('couponCodes'));
    }

    public function create(Request $request)
    {
        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.coupon.coupon-code-form', ['couponCode' => null])->render(),
            ]);
        }

        return view('admin.masters.coupon-code-create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'couponcode' => [
                'required',
                'string',
                'max:255',
                Rule::unique('couponcode', 'couponcode')
            ],
            'couponvalue' => 'required|numeric|min:0',
            'startdate' => 'nullable|date',
            'enddate' => 'nullable|date|after_or_equal:startdate',
            'fkcoupontypeid' => 'nullable|integer',
            'coupontype' => 'nullable|string|max:255',
            'fkcategoryid' => 'nullable|string',
            'isactive' => 'nullable',
            'ismultiuse' => 'nullable',
            'isgeneral' => 'nullable',
        ], [
            'couponcode.unique' => 'This coupon code already exists. Please choose a different code.',
            'couponcode.required' => 'Coupon Code is required.',
            'couponvalue.required' => 'Coupon Value is required.',
        ]);

        $validated['isactive'] = $request->has('isactive') ? 1 : 0;
        $validated['ismultiuse'] = $request->has('ismultiuse') ? 1 : 0;
        $validated['isgeneral'] = $request->has('isgeneral') ? 1 : 0;

        try {
            $couponCode = CouponCode::create($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Coupon code created successfully',
                    'data' => $couponCode
                ]);
            }

            return redirect()->route('admin.coupon-code')
                ->with('success', 'Coupon code created successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database constraint violations
            if ($e->getCode() == 23000) {
                $errorMessage = 'This coupon code already exists. Please choose a different code.';
            } else {
                $errorMessage = 'An error occurred while saving the coupon code.';
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['couponcode' => [$errorMessage]]
                ], 422);
            }

            return back()->withErrors(['couponcode' => $errorMessage])->withInput();
        }
    }

    public function show(Request $request, $id)
    {
        $couponCode = CouponCode::findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.coupon.coupon-code-view', compact('couponCode'))->render(),
            ]);
        }

        return view('admin.masters.coupon-code-view', compact('couponCode'));
    }

    public function edit(Request $request, $id)
    {
        $couponCode = CouponCode::findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.coupon.coupon-code-form', compact('couponCode'))->render(),
            ]);
        }

        return view('admin.masters.coupon-code-edit', compact('couponCode'));
    }

    public function update(Request $request, $id)
    {
        $couponCode = CouponCode::findOrFail($id);

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
            'couponcode' => [
                'required',
                'string',
                'max:255',
                Rule::unique('couponcode', 'couponcode')->ignore($couponCode->getKey(), $couponCode->getKeyName())
            ],
            'couponvalue' => 'required|numeric|min:0',
            'startdate' => 'nullable|date',
            'enddate' => 'nullable|date|after_or_equal:startdate',
            'fkcoupontypeid' => 'nullable|integer',
            'coupontype' => 'nullable|string|max:255',
            'fkcategoryid' => 'nullable|string',
            'isactive' => 'nullable',
            'ismultiuse' => 'nullable',
            'isgeneral' => 'nullable',
        ], [
            'couponcode.unique' => 'This coupon code already exists. Please choose a different code.',
            'couponcode.required' => 'Coupon Code is required.',
            'couponvalue.required' => 'Coupon Value is required.',
        ]);

        $validated['isactive'] = $request->has('isactive') ? 1 : 0;
        $validated['ismultiuse'] = $request->has('ismultiuse') ? 1 : 0;
        $validated['isgeneral'] = $request->has('isgeneral') ? 1 : 0;

        try {
            $couponCode->update($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Coupon code updated successfully',
                    'data' => $couponCode
                ]);
            }

            return redirect()->route('admin.coupon-code')
                ->with('success', 'Coupon code updated successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database constraint violations
            if ($e->getCode() == 23000) {
                $errorMessage = 'This coupon code already exists. Please choose a different code.';
            } else {
                $errorMessage = 'An error occurred while updating the coupon code.';
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['couponcode' => [$errorMessage]]
                ], 422);
            }

            return back()->withErrors(['couponcode' => $errorMessage])->withInput();
        }
    }

    public function destroy(Request $request, $id)
    {
        $couponCode = CouponCode::findOrFail($id);
        $couponCode->delete();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Coupon code deleted successfully'
            ]);
        }

        return redirect()->route('admin.coupon-code')
            ->with('success', 'Coupon code deleted successfully');
    }
}
