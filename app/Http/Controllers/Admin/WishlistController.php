<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $query = Wishlist::with(['product', 'customer']);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('customer', function($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('product', function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('productcode', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 25);
        $wishlists = $query->orderBy('createdon', 'desc')->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.wishlist.partials.table', compact('wishlists'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $wishlists])->render(),
            ]);
        }

        return view('admin.wishlist.index', compact('wishlists'));
    }
}
