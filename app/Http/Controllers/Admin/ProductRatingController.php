<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Exception;

class ProductRatingController extends Controller
{
    public function index(Request $request)
    {
        // Check if this is a DataTables request
        if ($request->has('draw')) {
            return $this->getDataTablesData($request);
        }

        // Load initial view (for non-DataTables requests)
        return view('admin.product-rate.index');
    }

    private function getDataTablesData(Request $request)
    {
        try {
            $query = ProductRating::with(['product', 'customer']);
            $filteredCountQuery = ProductRating::with(['product', 'customer']);

            // Get total records count
            $totalRecords = ProductRating::count();

            // Filter by rating
            $rating = $request->input('rating');
            if (!empty($rating)) {
                $query->where('rate', $rating);
                $filteredCountQuery->where('rate', $rating);
            }

            // Filter by published status
            $published = $request->input('published');
            if ($published !== '' && $published !== null) {
                $query->where('ispublished', $published);
                $filteredCountQuery->where('ispublished', $published);
            }

            // DataTables search (global search)
            $searchValue = $request->input('search.value', '');
            if (!empty($searchValue)) {
                $query->where(function($q) use ($searchValue) {
                    $q->whereHas('customer', function($customerQuery) use ($searchValue) {
                        $customerQuery->where('firstname', 'like', "%{$searchValue}%")
                          ->orWhere('lastname', 'like', "%{$searchValue}%");
                    })->orWhereHas('product', function($productQuery) use ($searchValue) {
                        $productQuery->where('title', 'like', "%{$searchValue}%");
                    });
                });

                $filteredCountQuery->where(function($q) use ($searchValue) {
                    $q->whereHas('customer', function($customerQuery) use ($searchValue) {
                        $customerQuery->where('firstname', 'like', "%{$searchValue}%")
                          ->orWhere('lastname', 'like', "%{$searchValue}%");
                    })->orWhereHas('product', function($productQuery) use ($searchValue) {
                        $productQuery->where('title', 'like', "%{$searchValue}%");
                    });
                });
            }

            // Get filtered count after search
            $filteredAfterSearch = $filteredCountQuery->count();

            // Ordering
            $orderColumnIndex = $request->input('order.0.column', 0);
            $orderDir = $request->input('order.0.dir', 'desc');

            // Default order by submiton
            $query->orderBy('submiton', $orderDir);

            // Pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 25);
            $ratings = $query->skip($start)->take($length)->get();

            // Format data for DataTables
            $data = [];
            $ratingBaseUrl = url('/admin/productrate');
            foreach ($ratings as $rating) {
                $customerName = $rating->customer ? trim(($rating->customer->firstname ?? '') . ' ' . ($rating->customer->lastname ?? '')) : 'N/A';
                $data[] = [
                    'product' => [
                        'title' => $rating->product ? $rating->product->title : 'N/A',
                        'photo' => $rating->product ? $rating->product->photo : null
                    ],
                    'customer' => $customerName ?: 'N/A',
                    'rate' => $rating->rate ?? 0,
                    'review' => $rating->review ?? 'No review',
                    'submiton' => $rating->submiton ? \Carbon\Carbon::parse($rating->submiton)->format('d/M/Y H:i') : 'N/A',
                    'ispublished' => $rating->ispublished ? 'Published' : 'Unpublished',
                    'action' => $rating->rateid // For action buttons
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredAfterSearch,
                'data' => $data
            ]);
        } catch (Exception $e) {
            Log::error('ProductRating DataTables Error: ' . $e->getMessage());
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
        $query = ProductRating::with(['product', 'customer']);

        // Apply same filters as index method
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('customer', function($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%");
            })->orWhereHas('product', function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%");
            });
        }

        if ($request->has('rating') && $request->rating) {
            $query->where('rate', $request->rating);
        }

        if ($request->has('published') && $request->published !== '') {
            $query->where('ispublished', $request->published);
        }

        $ratings = $query->orderBy('submiton', 'desc')->get();

        $filename = 'product_ratings_' . date('Y-m-d_His') . '.csv';

        $headers_array = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($ratings) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['Product Ratings Export']);
            fputcsv($file, ['Generated on: ' . date('d-M-Y H:i:s')]);
            fputcsv($file, []);

            fputcsv($file, [
                'Customer Name',
                'Product Code',
                'Product Name',
                'Rating',
                'Review',
                'Submitted Date',
                'Published'
            ]);

            foreach ($ratings as $rating) {
                $customerName = $rating->customer ? trim(($rating->customer->firstname ?? '') . ' ' . ($rating->customer->lastname ?? '')) : 'N/A';
                
                fputcsv($file, [
                    $customerName ?: 'N/A',
                    $rating->product->productcode ?? 'N/A',
                    $rating->product->title ?? 'N/A',
                    $rating->rate ?? 0,
                    $rating->review ?? '',
                    $rating->submiton ? \Carbon\Carbon::parse($rating->submiton)->format('d-M-Y H:i:s') : 'N/A',
                    $rating->ispublished ? 'Yes' : 'No'
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers_array);
    }
}
