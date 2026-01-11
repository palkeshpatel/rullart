<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Exception;

class ReturnRequestController extends Controller
{
    public function index(Request $request)
    {
        // Check if this is a DataTables request
        if ($request->has('draw')) {
            return $this->getDataTablesData($request);
        }

        // Load initial view (for non-DataTables requests)
        return view('admin.return-request.index');
    }

    private function getDataTablesData(Request $request)
    {
        try {
            $query = ReturnRequest::query();
            $filteredCountQuery = ReturnRequest::query();

            // Get total records count
            $totalRecords = ReturnRequest::count();

            // DataTables search (global search)
            $searchValue = $request->input('search.value', '');
            if (!empty($searchValue)) {
                $query->where(function($q) use ($searchValue) {
                    $q->where('firstname', 'like', "%{$searchValue}%")
                      ->orWhere('lastname', 'like', "%{$searchValue}%")
                      ->orWhere('email', 'like', "%{$searchValue}%")
                      ->orWhere('orderno', 'like', "%{$searchValue}%")
                      ->orWhere('mobile', 'like', "%{$searchValue}%")
                      ->orWhere('reason', 'like', "%{$searchValue}%");
                });

                $filteredCountQuery->where(function($q) use ($searchValue) {
                    $q->where('firstname', 'like', "%{$searchValue}%")
                      ->orWhere('lastname', 'like', "%{$searchValue}%")
                      ->orWhere('email', 'like', "%{$searchValue}%")
                      ->orWhere('orderno', 'like', "%{$searchValue}%")
                      ->orWhere('mobile', 'like', "%{$searchValue}%")
                      ->orWhere('reason', 'like', "%{$searchValue}%");
                });
            }

            // Get filtered count after search
            $filteredAfterSearch = $filteredCountQuery->count();

            // Ordering
            $orderColumnIndex = $request->input('order.0.column', 0);
            $orderDir = $request->input('order.0.dir', 'desc');

            $columns = [
                'firstname',
                'lastname',
                'email',
                'orderno',
                'mobile',
                'reason',
                'submiton',
                'requestid' // For action column
            ];

            $orderColumn = $columns[$orderColumnIndex] ?? 'submiton';
            $query->orderBy($orderColumn, $orderDir);

            // Pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 25);
            $returnRequests = $query->skip($start)->take($length)->get();

            // Format data for DataTables
            $data = [];
            $returnRequestBaseUrl = url('/admin/returnrequest');
            foreach ($returnRequests as $returnRequest) {
                $data[] = [
                    'firstname' => $returnRequest->firstname ?? 'N/A',
                    'lastname' => $returnRequest->lastname ?? 'N/A',
                    'email' => $returnRequest->email ?? 'N/A',
                    'orderno' => $returnRequest->orderno ?? 'N/A',
                    'mobile' => $returnRequest->mobile ?? 'N/A',
                    'reason' => $returnRequest->reason ?? 'N/A',
                    'submiton' => $returnRequest->submiton ? \Carbon\Carbon::parse($returnRequest->submiton)->format('d/M/Y H:i') : 'N/A',
                    'action' => $returnRequest->requestid ?? $returnRequest->id // For action buttons
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredAfterSearch,
                'data' => $data
            ]);
        } catch (Exception $e) {
            Log::error('ReturnRequest DataTables Error: ' . $e->getMessage());
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

    public function destroy($id)
    {
        try {
            $returnRequest = ReturnRequest::findOrFail($id);
            $returnRequest->delete();

            return response()->json([
                'success' => true,
                'message' => 'Return request deleted successfully.'
            ]);
        } catch (Exception $e) {
            Log::error('ReturnRequest Delete Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete return request.'
            ], 500);
        }
    }
}
