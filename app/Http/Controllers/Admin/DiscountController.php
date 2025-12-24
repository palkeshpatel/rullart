<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discount;
use Illuminate\Http\Request;

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
}
