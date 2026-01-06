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
            $query = ProductRating::with(['product']);
            $filteredCountQuery = ProductRating::with(['product']);

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
                    $q->whereHas('product', function($productQuery) use ($searchValue) {
                        $productQuery->where('title', 'like', "%{$searchValue}%");
                    })->orWhere('review', 'like', "%{$searchValue}%");
                });

                $filteredCountQuery->where(function($q) use ($searchValue) {
                    $q->whereHas('product', function($productQuery) use ($searchValue) {
                        $productQuery->where('title', 'like', "%{$searchValue}%");
                    })->orWhere('review', 'like', "%{$searchValue}%");
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
                $data[] = [
                    'product' => [
                        'title' => $rating->product ? $rating->product->title : 'N/A',
                        'photo' => $rating->product ? $rating->product->photo : null
                    ],
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

    public function edit($id)
    {
        try {
            $rating = ProductRating::with(['product'])->findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => [
                    'rateid' => $rating->rateid,
                    'rate' => $rating->rate,
                    'review' => $rating->review,
                    'ispublished' => $rating->ispublished,
                    'product_title' => $rating->product ? $rating->product->title : 'N/A'
                ]
            ]);
        } catch (Exception $e) {
            Log::error('ProductRating Edit Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Rating not found.'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'rate' => 'required|integer|min:1|max:5',
                'review' => 'nullable|string|max:1000',
                'ispublished' => 'required|boolean'
            ]);

            $rating = ProductRating::findOrFail($id);
            $rating->rate = $request->input('rate');
            $rating->review = $request->input('review', '');
            $rating->ispublished = $request->input('ispublished', 0);
            $rating->save();

            return response()->json([
                'success' => true,
                'message' => 'Rating updated successfully.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('ProductRating Update Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update rating.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $rating = ProductRating::findOrFail($id);
            $rating->delete();

            return response()->json([
                'success' => true,
                'message' => 'Rating deleted successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('ProductRating Delete Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete rating.'
            ], 500);
        }
    }
}
