<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('customer');

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('fkorderstatus', $request->status);
        }

        // Filter by country
        if ($request->has('country') && $request->country && $request->country !== '--All Country--') {
            $query->where('country', $request->country);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('orderid', 'like', "%{$search}%")
                  ->orWhere('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($customerQuery) use ($search) {
                      $customerQuery->where('email', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = $request->get('per_page', 25);
        $orders = $query->orderBy('orderdate', 'desc')->paginate($perPage);

        // Get unique countries for filter
        $countries = Order::select('country')->distinct()->whereNotNull('country')->orderBy('country')->pluck('country');

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.partials.orders-table', compact('orders'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $orders])->render(),
                'hasFilters' => $request->has('status') || $request->has('country') || $request->has('search'),
            ]);
        }

        return view('admin.orders', compact('orders', 'countries'));
    }
}
