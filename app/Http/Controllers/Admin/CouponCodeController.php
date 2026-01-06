<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CouponCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Exception;

class CouponCodeController extends Controller
{
    public function index(Request $request)
    {
        // Check if this is a DataTables request
        if ($request->has('draw')) {
            return $this->getDataTablesData($request);
        }

        // Return view for initial page load
        return view('admin.masters.coupon-code');
    }

    /**
     * Get DataTables data for server-side processing
     */
    private function getDataTablesData(Request $request)
    {
        try {
            $countQuery = CouponCode::query();
            $totalRecords = $countQuery->count();

            $query = CouponCode::query();
            $filteredCountQuery = CouponCode::query();

        // Filter by active status
        if ($request->has('active') && $request->active !== '') {
            $query->where('isactive', $request->active);
                $filteredCountQuery->where('isactive', $request->active);
        }

            // DataTables search
            $searchValue = $request->input('search.value', '');
            if (!empty($searchValue)) {
                $query->where('couponcode', 'like', "%{$searchValue}%");
                $filteredCountQuery->where('couponcode', 'like', "%{$searchValue}%");
            }

            $filteredAfterSearch = $filteredCountQuery->count();

            // Ordering
            $orderColumnIndex = $request->input('order.0.column', 0);
            $orderDir = $request->input('order.0.dir', 'desc');
            $columns = ['couponcodeid', 'couponcode', 'couponvalue', 'startdate', 'enddate', 'isactive', 'couponcodeid'];
            $orderColumn = $columns[$orderColumnIndex] ?? 'couponcodeid';
            $query->orderBy($orderColumn, $orderDir);

            // Pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 25);
            $couponCodes = $query->skip($start)->take($length)->get();

            // Format data
            $data = [];
            foreach ($couponCodes as $coupon) {
                $data[] = [
                    'couponcodeid' => $coupon->couponcodeid ?? '',
                    'couponcode' => $coupon->couponcode ?? '',
                    'couponvalue' => $coupon->couponvalue ?? '0',
                    'startdate' => $coupon->startdate ? \Carbon\Carbon::parse($coupon->startdate)->format('d-M-Y') : 'N/A',
                    'enddate' => $coupon->enddate ? \Carbon\Carbon::parse($coupon->enddate)->format('d-M-Y') : 'N/A',
                    'isactive' => $coupon->isactive ? 'Yes' : 'No',
                    'action' => $coupon->couponcodeid ?? ''
                ];
            }
            
            \Log::info('CouponCode DataTables Response', [
                'totalRecords' => $totalRecords,
                'filteredAfterSearch' => $filteredAfterSearch,
                'dataCount' => count($data),
                'data' => $data
            ]);

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredAfterSearch,
                'data' => $data
            ]);
        } catch (Exception $e) {
            Log::error('CouponCode DataTables Error: ' . $e->getMessage());
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
        // Get coupon types from coupontype table
        $couponTypes = \DB::table('coupontype')->orderBy('coupontype')->get(['coupontypeid', 'coupontype']);
        
        // Get categories for dropdown
        $categories = \App\Models\Category::where('ispublished', 1)->orderBy('category')->get(['categoryid', 'category']);
        
        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            try {
                $html = view('admin.masters.partials.coupon.coupon-code-form', [
                    'couponCode' => null,
                    'couponTypes' => $couponTypes ?? collect(),
                    'categories' => $categories ?? collect()
                ])->render();
                
                return response()->json([
                    'success' => true,
                    'html' => $html
                ]);
            } catch (\Exception $e) {
                \Log::error('Error rendering coupon form: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error loading form: ' . $e->getMessage()
                ], 500);
            }
        }

        return view('admin.masters.coupon-code-create', compact('couponTypes', 'categories'));
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
        
        // Set coupontype text from selected coupon type if not provided
        if (empty($validated['coupontype']) && !empty($validated['fkcoupontypeid'])) {
            $couponType = \DB::table('coupontype')->where('coupontypeid', $validated['fkcoupontypeid'])->first();
            if ($couponType) {
                $validated['coupontype'] = $couponType->coupontype;
            }
        }

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
        
        // Get coupon types from coupontype table
        $couponTypes = \DB::table('coupontype')->orderBy('coupontype')->get(['coupontypeid', 'coupontype']);
        
        // Get categories for dropdown
        $categories = \App\Models\Category::where('ispublished', 1)->orderBy('category')->get(['categoryid', 'category']);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson() || $request->header('X-Requested-With') === 'XMLHttpRequest') {
            try {
                $html = view('admin.masters.partials.coupon.coupon-code-form', [
                    'couponCode' => $couponCode,
                    'couponTypes' => $couponTypes ?? collect(),
                    'categories' => $categories ?? collect()
                ])->render();
                
                return response()->json([
                    'success' => true,
                    'html' => $html
                ]);
            } catch (\Exception $e) {
                \Log::error('Error rendering coupon form: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Error loading form: ' . $e->getMessage()
                ], 500);
            }
        }

        return view('admin.masters.coupon-code-edit', compact('couponCode', 'couponTypes', 'categories'));
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
        
        // Set coupontype text from selected coupon type if not provided
        if (empty($validated['coupontype']) && !empty($validated['fkcoupontypeid'])) {
            $couponType = \DB::table('coupontype')->where('coupontypeid', $validated['fkcoupontypeid'])->first();
            if ($couponType) {
                $validated['coupontype'] = $couponType->coupontype;
            }
        }

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
