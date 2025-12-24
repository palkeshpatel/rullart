<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourierCompany;
use Illuminate\Http\Request;

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
                'html' => view('admin.masters.partials.courier-company-table', compact('courierCompanies'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $courierCompanies])->render(),
            ]);
        }

        return view('admin.masters.courier-company', compact('courierCompanies'));
    }
}

