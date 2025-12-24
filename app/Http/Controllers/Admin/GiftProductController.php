<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class GiftProductController extends Controller
{
    public function index(Request $request)
    {
        // Gift products are typically products with a specific flag or category
        // For now, we'll show all products and you can filter as needed
        $query = Product::with('category');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('titleAR', 'like', "%{$search}%")
                  ->orWhere('productcode', 'like', "%{$search}%");
            });
        }

        // You can add specific filter for gift products if there's a flag in the database
        // $query->where('isgift', 1); // Example if there's an isgift column

        // Filter by category
        if ($request->filled('category') && $request->category !== '' && $request->category !== '--All Category--') {
            $query->where('fkcategoryid', $request->category);
        }

        $perPage = $request->get('per_page', 25);
        $products = $query->orderBy('productid', 'desc')->paginate($perPage);

        // Get categories for dropdown
        $categories = \App\Models\Category::orderBy('category')->get();

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.partials.gift-products-table', compact('products'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $products])->render(),
            ]);
        }

        return view('admin.gift-products', compact('products', 'categories'));
    }
}
