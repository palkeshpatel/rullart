<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

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

    public function export(Request $request)
    {
        $query = Wishlist::with(['product', 'customer']);

        // Apply same filters as index method
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

        $wishlists = $query->orderBy('createdon', 'desc')->get();

        $filename = 'wishlist_' . date('Y-m-d_His') . '.csv';

        $headers_array = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($wishlists) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['Wishlist Export']);
            fputcsv($file, ['Generated on: ' . date('d-M-Y H:i:s')]);
            fputcsv($file, []);

            fputcsv($file, [
                'Customer Name',
                'Customer Email',
                'Product Code',
                'Product Name',
                'Added Date'
            ]);

            foreach ($wishlists as $wishlist) {
                $customerName = $wishlist->customer ? trim(($wishlist->customer->firstname ?? '') . ' ' . ($wishlist->customer->lastname ?? '')) : 'N/A';
                $customerEmail = $wishlist->customer->email ?? 'N/A';
                
                fputcsv($file, [
                    $customerName ?: 'N/A',
                    $customerEmail,
                    $wishlist->product->productcode ?? 'N/A',
                    $wishlist->product->title ?? 'N/A',
                    $wishlist->createdon ? \Carbon\Carbon::parse($wishlist->createdon)->format('d-M-Y H:i:s') : 'N/A'
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers_array);
    }
}
