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

        $discounts = $query->orderBy('id', 'desc')->paginate(25);

        return view('admin.masters.discounts', compact('discounts'));
    }
}
