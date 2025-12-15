<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CouponCode;
use Illuminate\Http\Request;

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

        $couponCodes = $query->orderBy('couponcodeid', 'desc')->paginate(25);

        return view('admin.masters.coupon-code', compact('couponCodes'));
    }
}
