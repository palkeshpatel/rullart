<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ReturnRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = ReturnRequest::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('orderno', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 25);
        $returnRequests = $query->orderBy('submiton', 'desc')->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.return-request.partials.table', compact('returnRequests'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $returnRequests])->render(),
            ]);
        }

        return view('admin.return-request.index', compact('returnRequests'));
    }

    public function export(Request $request)
    {
        $query = ReturnRequest::query();

        // Apply same filters as index method
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('orderno', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $returnRequests = $query->orderBy('submiton', 'desc')->get();

        $filename = 'return_requests_' . date('Y-m-d_His') . '.csv';

        $headers_array = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($returnRequests) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['Return Requests Export']);
            fputcsv($file, ['Generated on: ' . date('d-M-Y H:i:s')]);
            fputcsv($file, []);

            fputcsv($file, [
                'First Name',
                'Last Name',
                'Email',
                'Mobile',
                'Order No',
                'Reason',
                'Language',
                'Submitted Date'
            ]);

            foreach ($returnRequests as $returnRequest) {
                fputcsv($file, [
                    $returnRequest->firstname ?? '',
                    $returnRequest->lastname ?? '',
                    $returnRequest->email ?? '',
                    $returnRequest->mobile ?? 'N/A',
                    $returnRequest->orderno ?? 'N/A',
                    $returnRequest->reason ?? '',
                    $returnRequest->lang ?? 'N/A',
                    $returnRequest->submiton ? \Carbon\Carbon::parse($returnRequest->submiton)->format('d-M-Y H:i:s') : 'N/A'
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers_array);
    }
}
