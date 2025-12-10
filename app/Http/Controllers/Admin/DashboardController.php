<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductRating;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get statistics
        $totalOrders = Order::count();
        $totalReturnRequests = ReturnRequest::count();
        $totalCustomers = Customer::count();
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalProductReviews = ProductRating::count();

        // Get last 10 orders
        $lastOrders = Order::with('customer')
            ->orderBy('orderdate', 'desc')
            ->limit(10)
            ->get();

        // Get last 10 customers
        $lastCustomers = Customer::orderBy('createdon', 'desc')
            ->limit(10)
            ->get();

        // Get last 10 product reviews
        $lastReviews = ProductRating::with(['product', 'customer'])
            ->orderBy('submiton', 'desc')
            ->limit(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalOrders',
            'totalReturnRequests',
            'totalCustomers',
            'totalProducts',
            'totalCategories',
            'totalProductReviews',
            'lastOrders',
            'lastCustomers',
            'lastReviews'
        ));
    }
}
