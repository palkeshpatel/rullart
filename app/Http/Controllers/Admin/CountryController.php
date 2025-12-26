<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CountryController extends Controller
{
    public function index(Request $request)
    {
        $query = Country::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('countryname', 'like', "%{$search}%")
                  ->orWhere('countrynameAR', 'like', "%{$search}%")
                  ->orWhere('isocode', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->has('active') && $request->active !== '') {
            $query->where('isactive', $request->active);
        }

        // Sorting
        $sortColumn = $request->get('sort', 'countryname');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortColumn, $sortDirection);

        $perPage = $request->get('per_page', 25);
        $countries = $query->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.countries.countries-table', compact('countries'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $countries])->render(),
            ]);
        }

        return view('admin.masters.countries', compact('countries'));
    }

    public function create(Request $request)
    {
        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.countries.country-form', ['country' => null])->render(),
            ]);
        }

        return view('admin.masters.country-create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'countryname' => [
                'required',
                'string',
                'max:255',
                Rule::unique('countrymaster', 'countryname')
            ],
            'countrynameAR' => 'nullable|string|max:255',
            'isocode' => 'nullable|string|max:10',
            'currencycode' => 'nullable|string|max:10',
            'currencyrate' => 'nullable|numeric',
            'currencysymbol' => 'nullable|string|max:10',
            'shipping_charge' => 'nullable|numeric',
            'free_shipping_over' => 'nullable|numeric',
            'shipping_days' => 'nullable|string|max:255',
            'shipping_daysAR' => 'nullable|string|max:255',
            'isactive' => 'nullable',
        ], [
            'countryname.unique' => 'This country name already exists. Please choose a different name.',
            'countryname.required' => 'Country Name(EN) is required.',
        ]);

        $validated['isactive'] = $request->has('isactive') ? 1 : 0;
        $validated['currencyrate'] = $validated['currencyrate'] ?? 0;
        $validated['shipping_charge'] = $validated['shipping_charge'] ?? 0;
        $validated['free_shipping_over'] = $validated['free_shipping_over'] ?? 0;

        try {
            $country = Country::create($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Country created successfully',
                    'data' => $country
                ]);
            }

            return redirect()->route('admin.countries')
                ->with('success', 'Country created successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database constraint violations
            if ($e->getCode() == 23000) {
                $errorMessage = 'This country name already exists. Please choose a different name.';
            } else {
                $errorMessage = 'An error occurred while saving the country.';
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['countryname' => [$errorMessage]]
                ], 422);
            }

            return back()->withErrors(['countryname' => $errorMessage])->withInput();
        }
    }

    public function show(Request $request, $id)
    {
        $country = Country::findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.countries.country-view', compact('country'))->render(),
            ]);
        }

        return view('admin.masters.country-view', compact('country'));
    }

    public function edit(Request $request, $id)
    {
        $country = Country::findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.countries.country-form', compact('country'))->render(),
            ]);
        }

        return view('admin.masters.country-edit', compact('country'));
    }

    public function update(Request $request, $id)
    {
        $country = Country::findOrFail($id);

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
            'countryname' => [
                'required',
                'string',
                'max:255',
                Rule::unique('countrymaster', 'countryname')->ignore($country->getKey(), $country->getKeyName())
            ],
            'countrynameAR' => 'nullable|string|max:255',
            'isocode' => 'nullable|string|max:10',
            'currencycode' => 'nullable|string|max:10',
            'currencyrate' => 'nullable|numeric',
            'currencysymbol' => 'nullable|string|max:10',
            'shipping_charge' => 'nullable|numeric',
            'free_shipping_over' => 'nullable|numeric',
            'shipping_days' => 'nullable|string|max:255',
            'shipping_daysAR' => 'nullable|string|max:255',
            'isactive' => 'nullable',
        ], [
            'countryname.unique' => 'This country name already exists. Please choose a different name.',
            'countryname.required' => 'Country Name(EN) is required.',
        ]);

        $validated['isactive'] = $request->has('isactive') ? 1 : 0;

        try {
            $country->update($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Country updated successfully',
                    'data' => $country
                ]);
            }

            return redirect()->route('admin.countries')
                ->with('success', 'Country updated successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database constraint violations
            if ($e->getCode() == 23000) {
                $errorMessage = 'This country name already exists. Please choose a different name.';
            } else {
                $errorMessage = 'An error occurred while updating the country.';
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['countryname' => [$errorMessage]]
                ], 422);
            }

            return back()->withErrors(['countryname' => $errorMessage])->withInput();
        }
    }

    public function destroy(Request $request, $id)
    {
        $country = Country::findOrFail($id);
        $country->delete();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Country deleted successfully'
            ]);
        }

        return redirect()->route('admin.countries')
            ->with('success', 'Country deleted successfully');
    }
}
