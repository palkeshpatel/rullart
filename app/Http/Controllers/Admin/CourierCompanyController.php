<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourierCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CourierCompanyController extends Controller
{
    public function index(Request $request)
    {
        $query = CourierCompany::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('tracking_url', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->has('active') && $request->active !== '') {
            $query->where('isactive', $request->active);
        }

        // Sorting
        $sortColumn = $request->get('sort', 'id');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        $perPage = $request->get('per_page', 25);
        $courierCompanies = $query->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.courier.courier-company-table', compact('courierCompanies'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $courierCompanies])->render(),
            ]);
        }

        return view('admin.masters.courier-company', compact('courierCompanies'));
    }

    public function create(Request $request)
    {
        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.courier.courier-company-form', ['courierCompany' => null])->render(),
            ]);
        }

        return view('admin.masters.courier-company-create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('courier_company', 'name')
            ],
            'tracking_url' => 'nullable|url|max:500',
            'isactive' => 'nullable',
        ], [
            'name.unique' => 'This courier company name already exists. Please choose a different name.',
            'name.required' => 'Courier Company Name is required.',
            'tracking_url.url' => 'Please enter a valid URL.',
        ]);

        $validated['isactive'] = $request->has('isactive') ? 1 : 0;
        $validated['created_at'] = now();

        try {
            $courierCompany = CourierCompany::create($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Courier company created successfully',
                    'data' => $courierCompany
                ]);
            }

            return redirect()->route('admin.courier-company')
                ->with('success', 'Courier company created successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database constraint violations
            if ($e->getCode() == 23000) {
                $errorMessage = 'This courier company name already exists. Please choose a different name.';
            } else {
                $errorMessage = 'An error occurred while saving the courier company.';
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['name' => [$errorMessage]]
                ], 422);
            }

            return back()->withErrors(['name' => $errorMessage])->withInput();
        }
    }

    public function show(Request $request, $id)
    {
        $courierCompany = CourierCompany::findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.courier.courier-company-view', compact('courierCompany'))->render(),
            ]);
        }

        return view('admin.masters.courier-company-view', compact('courierCompany'));
    }

    public function edit(Request $request, $id)
    {
        $courierCompany = CourierCompany::findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.courier.courier-company-form', compact('courierCompany'))->render(),
            ]);
        }

        return view('admin.masters.courier-company-edit', compact('courierCompany'));
    }

    public function update(Request $request, $id)
    {
        $courierCompany = CourierCompany::findOrFail($id);

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
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('courier_company', 'name')->ignore($courierCompany->getKey(), $courierCompany->getKeyName())
            ],
            'tracking_url' => 'nullable|url|max:500',
            'isactive' => 'nullable',
        ], [
            'name.unique' => 'This courier company name already exists. Please choose a different name.',
            'name.required' => 'Courier Company Name is required.',
            'tracking_url.url' => 'Please enter a valid URL.',
        ]);

        $validated['isactive'] = $request->has('isactive') ? 1 : 0;

        try {
            $courierCompany->update($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Courier company updated successfully',
                    'data' => $courierCompany
                ]);
            }

            return redirect()->route('admin.courier-company')
                ->with('success', 'Courier company updated successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database constraint violations
            if ($e->getCode() == 23000) {
                $errorMessage = 'This courier company name already exists. Please choose a different name.';
            } else {
                $errorMessage = 'An error occurred while updating the courier company.';
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['name' => [$errorMessage]]
                ], 422);
            }

            return back()->withErrors(['name' => $errorMessage])->withInput();
        }
    }

    public function destroy(Request $request, $id)
    {
        $courierCompany = CourierCompany::findOrFail($id);
        $courierCompany->delete();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Courier company deleted successfully'
            ]);
        }

        return redirect()->route('admin.courier-company')
            ->with('success', 'Courier company deleted successfully');
    }
}
