<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShoppingCart;
use App\Models\Customer;
use Illuminate\Http\Request;

class ShoppingCartController extends Controller
{
    public function index(Request $request)
    {
        $query = ShoppingCart::with('customer');

        // Filter incomplete carts (status != completed or null)
        $query->where(function($q) {
            $q->whereNull('fkstatusid')
              ->orWhere('fkstatusid', '!=', 1); // Assuming 1 is completed status
        });

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('customer', function($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('shoppingcartid', 'like', "%{$search}%");
        }

        // Filter by country if provided
        if ($request->has('country') && $request->country && $request->country !== '--All Country--') {
            $query->whereHas('customer', function($q) use ($request) {
                $q->where('country', $request->country);
            });
        }

        $perPage = $request->get('per_page', 25);
        $carts = $query->orderBy('updatedon', 'desc')->paginate($perPage);

        // Get unique countries for filter
        $countries = Customer::select('country')
            ->distinct()
            ->whereNotNull('country')
            ->orderBy('country')
            ->pluck('country');

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.orders-not-process.partials.table', compact('carts'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $carts])->render(),
            ]);
        }

        return view('admin.orders-not-process.index', compact('carts', 'countries'));
    }
}
