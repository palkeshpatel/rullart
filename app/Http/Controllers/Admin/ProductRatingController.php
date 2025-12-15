<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductRating;
use Illuminate\Http\Request;

class ProductRatingController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductRating::with(['product', 'customer']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('customer', function($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%");
            })->orWhereHas('product', function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        // Filter by rating
        if ($request->has('rating') && $request->rating) {
            $query->where('rate', $request->rating);
        }

        // Filter by published status
        if ($request->has('published') && $request->published !== '') {
            $query->where('ispublished', $request->published);
        }

        $ratings = $query->orderBy('submiton', 'desc')->paginate(25);

        return view('admin.product-rate', compact('ratings'));
    }
}
