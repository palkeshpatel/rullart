<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Response;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 25);
        $customers = $query->orderBy('createdon', 'desc')->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.partials.customers-table', compact('customers'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $customers])->render(),
            ]);
        }

        return view('admin.customers', compact('customers'));
    }

    public function create(Request $request)
    {
        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.partials.customer-form', ['customer' => null])->render(),
            ]);
        }

        return view('admin.customer-create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'firstname' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('customers', 'email')
            ],
            'password' => 'required|string|min:6',
            'mobile' => 'nullable|string|max:20',
            'isactive' => 'nullable',
        ], [
            'firstname.required' => 'First name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 6 characters.',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['isactive'] = $request->has('isactive') ? 1 : 0;
        $validated['createdon'] = now();
        $validated['updateddate'] = now();

        try {
            $customer = Customer::create($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Customer created successfully',
                    'data' => $customer
                ]);
            }

            return redirect()->route('admin.customers')
                ->with('success', 'Customer created successfully');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while saving the customer.',
                    'errors' => ['general' => ['An error occurred while saving the customer.']]
                ], 422);
            }

            return back()->withErrors(['general' => 'An error occurred while saving the customer.'])->withInput();
        }
    }

    public function show(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.partials.customer-view', compact('customer'))->render(),
            ]);
        }

        return view('admin.customer-view', compact('customer'));
    }

    public function edit(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.partials.customer-form', compact('customer'))->render(),
            ]);
        }

        return view('admin.customer-edit', compact('customer'));
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

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
            'firstname' => 'required|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($id, 'customerid')
            ],
            'password' => 'nullable|string|min:6',
            'mobile' => 'nullable|string|max:20',
            'isactive' => 'nullable',
        ], [
            'firstname.required' => 'First name is required.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'This email is already registered.',
            'password.min' => 'Password must be at least 6 characters.',
        ]);

        // Only update password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['isactive'] = $request->has('isactive') ? 1 : 0;
        $validated['updateddate'] = now();

        try {
            $customer->update($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Customer updated successfully',
                    'data' => $customer
                ]);
            }

            return redirect()->route('admin.customers')
                ->with('success', 'Customer updated successfully');
        } catch (\Exception $e) {
            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while updating the customer.',
                    'errors' => ['general' => ['An error occurred while updating the customer.']]
                ], 422);
            }

            return back()->withErrors(['general' => 'An error occurred while updating the customer.'])->withInput();
        }
    }

    public function export(Request $request)
    {
        $query = Customer::query();

        // Apply same search filter as index method
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Get all customers (no pagination for export)
        $customers = $query->orderBy('createdon', 'desc')->get();

        $filename = 'customers_' . date('Y-m-d_His') . '.csv';

        $headers_array = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        // Add BOM for UTF-8 to ensure Excel opens it correctly
        $callback = function () use ($customers) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Add title
            fputcsv($file, ['Customers Export']);
            fputcsv($file, ['Generated on: ' . date('d-M-Y H:i:s')]);
            fputcsv($file, []); // Empty row

            // Add headers
            fputcsv($file, [
                'Customer ID',
                'First Name',
                'Last Name',
                'Email',
                'Registration Date',
                'Last Login',
                'Status',
                'Language',
                'Newsletter'
            ]);

            // Add data
            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->customerid ?? 'N/A',
                    $customer->firstname ?? '',
                    $customer->lastname ?? '',
                    $customer->email ?? '',
                    $customer->createdon ? \Carbon\Carbon::parse($customer->createdon)->format('d-M-Y H:i:s') : 'N/A',
                    $customer->last_login ? \Carbon\Carbon::parse($customer->last_login)->format('d-M-Y H:i:s') : 'N/A',
                    $customer->isactive ? 'Active' : 'Inactive',
                    $customer->language ?? 'N/A',
                    $customer->isnewsletter ? 'Yes' : 'No'
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers_array);
    }
}
