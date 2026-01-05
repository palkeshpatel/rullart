<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Exception;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        // Check if this is a DataTables request
        if ($request->has('draw')) {
            return $this->getDataTablesData($request);
        }

        // Load initial view (for non-DataTables requests)
        return view('admin.wishlist.index');
    }

    private function getDataTablesData(Request $request)
    {
        try {
            $query = Wishlist::with(['product', 'customer']);
            $filteredCountQuery = Wishlist::with(['product', 'customer']);

            // Get total records count
            $totalRecords = Wishlist::count();

            // DataTables search (global search)
            $searchValue = $request->input('search.value', '');
            if (!empty($searchValue)) {
                $query->where(function($q) use ($searchValue) {
                    $q->whereHas('customer', function($customerQuery) use ($searchValue) {
                        $customerQuery->where('firstname', 'like', "%{$searchValue}%")
                          ->orWhere('lastname', 'like', "%{$searchValue}%")
                          ->orWhere('email', 'like', "%{$searchValue}%");
                    })->orWhereHas('product', function($productQuery) use ($searchValue) {
                        $productQuery->where('title', 'like', "%{$searchValue}%")
                          ->orWhere('productcode', 'like', "%{$searchValue}%");
                    });
                });

                $filteredCountQuery->where(function($q) use ($searchValue) {
                    $q->whereHas('customer', function($customerQuery) use ($searchValue) {
                        $customerQuery->where('firstname', 'like', "%{$searchValue}%")
                          ->orWhere('lastname', 'like', "%{$searchValue}%")
                          ->orWhere('email', 'like', "%{$searchValue}%");
                    })->orWhereHas('product', function($productQuery) use ($searchValue) {
                        $productQuery->where('title', 'like', "%{$searchValue}%")
                          ->orWhere('productcode', 'like', "%{$searchValue}%");
                    });
                });
            }

            // Get filtered count after search
            $filteredAfterSearch = $filteredCountQuery->count();

            // Ordering
            $orderColumnIndex = $request->input('order.0.column', 0);
            $orderDir = $request->input('order.0.dir', 'desc');

            // Default order by createdon
            $query->orderBy('wishlist.createdon', $orderDir);

            // Pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 25);
            $wishlists = $query->skip($start)->take($length)->get();

            // Format data for DataTables
            $data = [];
            $wishlistBaseUrl = url('/admin/wishlist');
            foreach ($wishlists as $wishlist) {
                $customerName = $wishlist->customer ? trim(($wishlist->customer->firstname ?? '') . ' ' . ($wishlist->customer->lastname ?? '')) : 'N/A';
                $data[] = [
                    'product' => [
                        'title' => $wishlist->product ? $wishlist->product->title : 'N/A',
                        'productcode' => $wishlist->product ? $wishlist->product->productcode : 'N/A',
                        'photo' => $wishlist->product ? $wishlist->product->photo : null
                    ],
                    'customer' => $customerName ?: 'N/A',
                    'email' => $wishlist->customer ? $wishlist->customer->email : 'N/A',
                    'createdon' => $wishlist->createdon ? \Carbon\Carbon::parse($wishlist->createdon)->format('d/M/Y H:i') : 'N/A',
                    'action' => $wishlist->wishlistid // For action buttons
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredAfterSearch,
                'data' => $data
            ]);
        } catch (Exception $e) {
            Log::error('Wishlist DataTables Error: ' . $e->getMessage());
            return response()->json([
                'draw' => intval($request->input('draw', 1)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'An error occurred while loading data.'
            ], 500);
        }
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
