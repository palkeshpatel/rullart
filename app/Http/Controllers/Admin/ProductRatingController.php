<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

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

        $perPage = $request->get('per_page', 25);
        $ratings = $query->orderBy('submiton', 'desc')->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.product-rate.partials.table', compact('ratings'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $ratings])->render(),
            ]);
        }

        return view('admin.product-rate.index', compact('ratings'));
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
