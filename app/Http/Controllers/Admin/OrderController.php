<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Exception;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        // Check if this is a DataTables request
        if ($request->has('draw')) {
            return $this->getDataTablesData($request);
        }

        // Get unique countries for filter
        $countries = Order::select('country')->distinct()->whereNotNull('country')->orderBy('country')->pluck('country');

        // Return view for initial page load
        return view('admin.orders.index', compact('countries'));
    }

    /**
     * Get DataTables data for server-side processing
     */
    private function getDataTablesData(Request $request)
    {
        try {
            // Base query for counting
            $countQuery = Order::query();

            // Get total records count (before filtering)
            $totalRecords = $countQuery->count();

            // Build base query for data - join customers table for email sorting
            $query = Order::select('ordermaster.*')
                ->leftJoin('customers', 'ordermaster.fkcustomerid', '=', 'customers.customerid');

            // Build count query for filtered results - join customers for email search
            $filteredCountQuery = Order::select('ordermaster.orderid')
                ->leftJoin('customers', 'ordermaster.fkcustomerid', '=', 'customers.customerid');

        // Filter by status
            $status = $request->input('status');
            if (!empty($status)) {
                $query->where('ordermaster.fkorderstatus', $status);
                $filteredCountQuery->where('ordermaster.fkorderstatus', $status);
        }

        // Filter by country
            $country = $request->input('country');
            if (!empty($country) && $country !== '--All Country--') {
                $query->where('ordermaster.country', $country);
                $filteredCountQuery->where('ordermaster.country', $country);
        }

            // Get filtered count (after filters but before search)
            $filteredCount = $filteredCountQuery->distinct('ordermaster.orderid')->count('ordermaster.orderid');

            // DataTables search (global search)
            $searchValue = $request->input('search.value', '');
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('ordermaster.orderid', 'like', "%{$searchValue}%")
                        ->orWhere('ordermaster.firstname', 'like', "%{$searchValue}%")
                        ->orWhere('ordermaster.lastname', 'like', "%{$searchValue}%")
                        ->orWhere('ordermaster.mobile', 'like', "%{$searchValue}%")
                        ->orWhere('customers.email', 'like', "%{$searchValue}%");
                });

                // Apply same search to count query
                $filteredCountQuery->where(function ($q) use ($searchValue) {
                    $q->where('ordermaster.orderid', 'like', "%{$searchValue}%")
                        ->orWhere('ordermaster.firstname', 'like', "%{$searchValue}%")
                        ->orWhere('ordermaster.lastname', 'like', "%{$searchValue}%")
                        ->orWhere('ordermaster.mobile', 'like', "%{$searchValue}%")
                        ->orWhere('customers.email', 'like', "%{$searchValue}%");
                  });
            }

            // Get filtered count after search
            $filteredAfterSearch = $filteredCountQuery->distinct('ordermaster.orderid')->count('ordermaster.orderid');

            // Ordering
            $orderColumnIndex = $request->input('order.0.column', 0);
            $orderDir = $request->input('order.0.dir', 'desc');

            $columns = [
                'ordermaster.orderid',
                'ordermaster.firstname',
                'customers.email',  // Use joined table column
                'ordermaster.total',
                'ordermaster.fkorderstatus',
                'ordermaster.country',
                'ordermaster.orderdate',
                'ordermaster.shipping_charge',
                'ordermaster.paymentmethod',
                'ordermaster.tranid'
            ];

            $orderColumn = $columns[$orderColumnIndex] ?? 'ordermaster.orderdate';
            $query->orderBy($orderColumn, $orderDir);

            // Pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 25);
            $orders = $query->skip($start)->take($length)->get();

            // Format data for DataTables
            $data = [];
            foreach ($orders as $order) {
                // Load customer relationship if not already loaded
                if (!$order->relationLoaded('customer')) {
                    $order->load('customer');
                }
                
                $data[] = [
                    'orderid' => $order->orderid ?? '',
                    'name' => trim(($order->firstname ?? '') . ' ' . ($order->lastname ?? '')),
                    'email' => $order->customer->email ?? 'N/A',
                    'total' => number_format($order->total ?? 0, 3),
                    'status' => $order->fkorderstatus ?? 1,
                    'country' => $order->country ?? 'N/A',
                    'orderdate' => $order->orderdate ? \Carbon\Carbon::parse($order->orderdate)->format('d/M/Y H:i') : 'N/A',
                    'shippingmethod' => $order->shipping_charge ? 'standard' : 'N/A',
                    'paymentmethod' => $order->paymentmethod ?? 'N/A',
                    'ref' => $order->tranid ?? 'N/A',
                    'action' => $order->orderid // For action buttons
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredAfterSearch,
                'data' => $data
            ]);
        } catch (Exception $e) {
            Log::error('Order DataTables Error: ' . $e->getMessage());
            return response()->json([
                'draw' => intval($request->input('draw', 1)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'An error occurred while loading data.'
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        $order = Order::with(['customer', 'items.product'])
            ->where('orderid', $id)
            ->firstOrFail();

        // Ensure items are loaded - try both relationship and direct query
        $items = OrderItem::where('fkorderid', $order->orderid)
            ->with('product')
            ->get();
        
        // Set items collection on order object
        $order->setRelation('items', $items);

        // Get order statuses for dropdown
        $orderStatuses = DB::table('orderstatus')->get();

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.orders.partials.modal', compact('order', 'orderStatuses'))->render(),
            ]);
        }

        return view('admin.orders.show', compact('order', 'orderStatuses'));
    }

    public function edit($id)
    {
        $order = Order::with(['customer', 'items.product'])
            ->where('orderid', $id)
            ->firstOrFail();

        // Ensure items are loaded - try both relationship and direct query
        $items = OrderItem::where('fkorderid', $order->orderid)
            ->with('product')
            ->get();
        
        // Set items collection on order object
        $order->setRelation('items', $items);

        // Get order statuses for dropdown
        $orderStatuses = DB::table('orderstatus')->get();

        return view('admin.orders.edit', compact('order', 'orderStatuses'));
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // Update order status
        if ($request->has('fkorderstatus')) {
            $order->fkorderstatus = $request->fkorderstatus;
        }

        // Update tracking number
        if ($request->has('trackingno')) {
            $order->trackingno = $request->trackingno;
        }

        $order->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully'
            ]);
        }

        return redirect()->route('admin.orders.edit', $id)
            ->with('success', 'Order updated successfully');
    }

    public function export(Request $request)
    {
        $query = Order::with('customer');

        // Apply same filters as index method
        if ($request->has('status') && $request->status) {
            $query->where('fkorderstatus', $request->status);
        }

        if ($request->has('country') && $request->country && $request->country !== '--All Country--') {
            $query->where('country', $request->country);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('orderid', 'like', "%{$search}%")
                  ->orWhere('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($customerQuery) use ($search) {
                      $customerQuery->where('email', 'like', "%{$search}%");
                  });
            });
        }

        $orders = $query->orderBy('orderdate', 'desc')->get();

        $filename = 'orders_' . date('Y-m-d_His') . '.csv';

        $headers_array = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            
            // Add UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['Orders Export']);
            fputcsv($file, ['Generated on: ' . date('d-M-Y H:i:s')]);
            fputcsv($file, []);

            fputcsv($file, [
                'Order ID',
                'Order Date',
                'Customer Name',
                'Customer Email',
                'First Name',
                'Last Name',
                'Mobile',
                'Country',
                'Total Amount',
                'Status',
                'Tracking No'
            ]);

            foreach ($orders as $order) {
                $customerName = $order->customer ? trim(($order->customer->firstname ?? '') . ' ' . ($order->customer->lastname ?? '')) : 'N/A';
                $customerEmail = $order->customer->email ?? 'N/A';
                
                fputcsv($file, [
                    $order->orderid ?? 'N/A',
                    $order->orderdate ? \Carbon\Carbon::parse($order->orderdate)->format('d-M-Y H:i:s') : 'N/A',
                    $customerName ?: 'N/A',
                    $customerEmail,
                    $order->firstname ?? '',
                    $order->lastname ?? '',
                    $order->mobile ?? 'N/A',
                    $order->country ?? 'N/A',
                    number_format($order->totalamount ?? 0, 3),
                    $order->fkorderstatus ?? 'N/A',
                    $order->trackingno ?? 'N/A'
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers_array);
    }
}
