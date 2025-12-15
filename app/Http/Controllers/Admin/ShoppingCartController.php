<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShoppingCart;
use Illuminate\Http\Request;

class ShoppingCartController extends Controller
{
    public function index(Request $request)
    {
        $query = ShoppingCart::with('customer');

        // Filter incomplete carts (status != completed)
        $query->where('fkstatusid', '!=', 1); // Assuming 1 is completed status

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('customer', function($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhere('shoppingcartid', 'like', "%{$search}%");
        }

        $carts = $query->orderBy('updatedon', 'desc')->paginate(25);

        return view('admin.orders-not-process', compact('carts'));
    }
}
