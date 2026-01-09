<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Exception;

class CountryController extends Controller
{
    public function index(Request $request)
    {
        // Check if this is a DataTables request
        if ($request->has('draw')) {
            return $this->getDataTablesData($request);
        }

        // Return view for initial page load
        return view('admin.masters.countries');
    }

    /**
     * Get DataTables data for server-side processing
     */
    private function getDataTablesData(Request $request)
    {
        try {
            // Get total count of all countries (for recordsTotal)
            $countQuery = Country::query();
            $totalRecords = $countQuery->count();

            $query = Country::query();
            $filteredCountQuery = Country::query();

            // Filter by active status
            // Admin panel shows all countries by default (when --All-- is selected or filter not set)
            // When filter is explicitly set to '1' (Yes) or '0' (No), apply the filter
            $activeFilter = $request->input('active', '');
            if ($activeFilter === '1' || $activeFilter === '0' || $activeFilter === 1 || $activeFilter === 0) {
                // User selected a specific filter (Yes=1 or No=0)
                $activeValue = (int)$activeFilter;
                $query->where('isactive', $activeValue);
                $filteredCountQuery->where('isactive', $activeValue);
            }
            // If empty string, null, or '--All--', don't filter (show all countries)

            // DataTables search
            $searchValue = $request->input('search.value', '');
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('countryname', 'like', "%{$searchValue}%")
                        ->orWhere('countrynameAR', 'like', "%{$searchValue}%")
                        ->orWhere('isocode', 'like', "%{$searchValue}%");
                });

                $filteredCountQuery->where(function ($q) use ($searchValue) {
                    $q->where('countryname', 'like', "%{$searchValue}%")
                        ->orWhere('countrynameAR', 'like', "%{$searchValue}%")
                        ->orWhere('isocode', 'like', "%{$searchValue}%");
                });
            }

            $filteredAfterSearch = $filteredCountQuery->count();

            // Ordering - default by countryid ASC
            $orderColumnIndex = $request->input('order.0.column', 8); // Default to countryid column (index 8)
            $orderDir = $request->input('order.0.dir', 'asc');
            $columns = ['countryname', 'countrynameAR', 'currencycode', 'currencyrate', 'shipping_charge', 'free_shipping_over', 'isocode', 'isactive', 'countryid'];
            $orderColumn = $columns[$orderColumnIndex] ?? 'countryid';
            $query->orderBy($orderColumn, $orderDir);

            // Pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 25);
            $countries = $query->skip($start)->take($length)->get();

            // Log for debugging
            Log::info('Country DataTables Query', [
                'total_records' => $totalRecords,
                'filtered_count' => $filteredAfterSearch,
                'countries_found' => $countries->count(),
                'active_filter' => $activeFilter,
                'query_sql' => $query->toSql(),
                'query_bindings' => $query->getBindings()
            ]);

            // Format data
            $data = [];
            foreach ($countries as $country) {
                $data[] = [
                    'countryname' => $country->countryname ?? '',
                    'countrynameAR' => $country->countrynameAR ?? 'N/A',
                    'currencycode' => $country->currencycode ?? 'N/A',
                    'currencyrate' => number_format($country->currencyrate ?? 0, 6),
                    'shipping_charge' => number_format($country->shipping_charge ?? 0, 2),
                    'free_shipping_over' => number_format($country->free_shipping_over ?? 0, 3),
                    'isocode' => $country->isocode ?? 'N/A',
                    'isactive' => $country->isactive ? 'Yes' : 'No',
                    'action' => $country->countryid
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredAfterSearch,
                'data' => $data
            ]);
        } catch (Exception $e) {
            Log::error('Country DataTables Error: ' . $e->getMessage());
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
            'countrynameAR' => 'required|string|max:255',
            'isocode' => 'nullable|string|max:10',
            'currencycode' => 'required|string|max:10',
            'currencyrate' => 'required|numeric',
            'currencysymbol' => 'nullable|string|max:10',
            'shipping_charge' => 'nullable|numeric',
            'free_shipping_over' => 'nullable|numeric',
            'shipping_days' => 'nullable|string|max:255',
            'shipping_daysAR' => 'nullable|string|max:255',
            'isactive' => 'nullable',
        ], [
            'countryname.unique' => 'This country name already exists. Please choose a different name.',
            'countryname.required' => 'Country Name (EN) is required.',
            'countrynameAR.required' => 'Country Name (AR) is required.',
            'currencycode.required' => 'Currency Code is required.',
            'currencyrate.required' => 'Currency Rate is required.',
            'currencyrate.numeric' => 'Currency Rate must be a number.',
        ]);

        $validated['isactive'] = $request->has('isactive') ? 1 : 0;
        // Currency rate is required, so no default needed
        $validated['shipping_charge'] = $validated['shipping_charge'] ?? 0;
        $validated['free_shipping_over'] = $validated['free_shipping_over'] ?? 0;
        // Set default values for nullable fields that might not have defaults in DB
        // These fields must be present in the insert even if empty
        if (!isset($validated['currencysymbol'])) {
            $validated['currencysymbol'] = '';
        }
        if (!isset($validated['shipping_days'])) {
            $validated['shipping_days'] = '';
        }
        if (!isset($validated['shipping_daysAR'])) {
            $validated['shipping_daysAR'] = '';
        }
        if (!isset($validated['isocode'])) {
            $validated['isocode'] = '';
        }

        try {
            // Get the next countryid (since it's not auto-increment)
            $maxCountryId = Country::max('countryid') ?? 0;
            $validated['countryid'] = $maxCountryId + 1;

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
            Log::error('Country creation QueryException: ' . $e->getMessage());
            Log::error('SQL Error Code: ' . $e->getCode());
            Log::error('SQL State: ' . ($e->errorInfo[0] ?? 'N/A'));
            Log::error('SQL Error: ' . ($e->errorInfo[2] ?? 'N/A'));

            $errorMessage = 'An error occurred while saving the country.';
            $errorField = 'countryname';

            if ($e->getCode() == 23000) {
                // Check which constraint was violated
                $errorInfo = $e->errorInfo[2] ?? '';
                if (stripos($errorInfo, 'countryname') !== false) {
                    $errorMessage = 'This country name already exists. Please choose a different name.';
                    $errorField = 'countryname';
                } elseif (stripos($errorInfo, 'currencycode') !== false) {
                    $errorMessage = 'This currency code already exists. Please choose a different code.';
                    $errorField = 'currencycode';
                } else {
                    $errorMessage = 'A database constraint violation occurred: ' . $errorInfo;
                }
            } else {
                $errorMessage = 'Database error: ' . ($e->errorInfo[2] ?? $e->getMessage());
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => [$errorField => [$errorMessage]],
                    'debug' => config('app.debug') ? [
                        'sql_error' => $e->errorInfo[2] ?? $e->getMessage(),
                        'code' => $e->getCode()
                    ] : []
                ], 422);
            }

            return back()->withErrors([$errorField => $errorMessage])->withInput();
        } catch (\Exception $e) {
            Log::error('Country creation error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            $errorMessage = 'An unexpected error occurred: ' . $e->getMessage();

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['countryname' => [$errorMessage]]
                ], 500);
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
            'countryname' => [
                'required',
                'string',
                'max:255',
                Rule::unique('countrymaster', 'countryname')->ignore($country->getKey(), $country->getKeyName())
            ],
            'countrynameAR' => 'required|string|max:255',
            'isocode' => 'nullable|string|max:10',
            'currencycode' => 'required|string|max:10',
            'currencyrate' => 'required|numeric',
            'currencysymbol' => 'nullable|string|max:10',
            'shipping_charge' => 'nullable|numeric',
            'free_shipping_over' => 'nullable|numeric',
            'shipping_days' => 'nullable|string|max:255',
            'shipping_daysAR' => 'nullable|string|max:255',
            'isactive' => 'nullable',
        ], [
            'countryname.unique' => 'This country name already exists. Please choose a different name.',
            'countryname.required' => 'Country Name (EN) is required.',
            'countrynameAR.required' => 'Country Name (AR) is required.',
            'currencycode.required' => 'Currency Code is required.',
            'currencyrate.required' => 'Currency Rate is required.',
            'currencyrate.numeric' => 'Currency Rate must be a number.',
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

    /**
     * Update currency rates for all countries using free API
     * Uses exchangerate-api.com (free tier, no API key required)
     */
    public function updateCurrencyRate(Request $request)
    {
        try {
            // Get all active countries
            $countries = Country::where('isactive', 1)->get();
            $updatedCount = 0;
            $errors = [];
            $baseCurrency = 'KWD'; // Base currency is Kuwaiti Dinar

            // Fetch exchange rates from free API (exchangerate-api.com)
            // Free tier: 1,500 requests/month, no API key required
            $apiUrl = "https://api.exchangerate-api.com/v4/latest/{$baseCurrency}";

            $context = stream_context_create([
                'http' => [
                    'timeout' => 10,
                    'method' => 'GET',
                    'header' => 'User-Agent: Laravel Currency Updater'
                ]
            ]);

            $response = @file_get_contents($apiUrl, false, $context);

            if ($response === false) {
                throw new Exception('Failed to fetch currency rates from API. Please check your internet connection.');
            }

            $data = json_decode($response, true);

            if (!isset($data['rates']) || !is_array($data['rates'])) {
                throw new Exception('Invalid response from currency API.');
            }

            $rates = $data['rates'];

            // Update currency rates for each country
            foreach ($countries as $country) {
                if (empty($country->currencycode)) {
                    continue;
                }

                $currencyCode = strtoupper(trim($country->currencycode));

                // Skip if same as base currency (KWD = 1.0)
                if ($currencyCode === $baseCurrency) {
                    $country->currencyrate = 1.0;
                    $country->save();
                    $updatedCount++;
                    continue;
                }

                // Check if rate exists in API response
                if (isset($rates[$currencyCode])) {
                    $newRate = (float) $rates[$currencyCode];

                    if ($newRate > 0) {
                        $country->currencyrate = $newRate;
                        $country->save();
                        $updatedCount++;
                    } else {
                        $errors[] = "Invalid rate for {$country->countryname} ({$currencyCode})";
                    }
                } else {
                    $errors[] = "Rate not found for {$country->countryname} ({$currencyCode})";
                }
            }

            $message = "Currency rates updated successfully. {$updatedCount} countries updated.";
            if (!empty($errors)) {
                $message .= " Errors: " . implode(', ', array_slice($errors, 0, 5));
                if (count($errors) > 5) {
                    $message .= " and " . (count($errors) - 5) . " more.";
                }
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'updated_count' => $updatedCount,
                    'errors' => $errors
                ]);
            }

            return redirect()->route('admin.countries')
                ->with('success', $message);
        } catch (Exception $e) {
            Log::error('Currency Rate Update Error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating currency rates: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.countries')
                ->with('error', 'An error occurred while updating currency rates: ' . $e->getMessage());
        }
    }
}
