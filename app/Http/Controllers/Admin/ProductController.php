<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
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

        // Filter by category
        if ($request->has('category') && $request->category) {
            $query->where('fkcategoryid', $request->category);
        }

        // Filter by published status
        if ($request->has('published') && $request->published !== '') {
            $query->where('ispublished', $request->published);
        }

        $products = $query->orderBy('productid', 'desc')->paginate(25);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.partials.products-table', compact('products'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $products])->render(),
            ]);
        }

        return view('admin.products', compact('products'));
    }
}
