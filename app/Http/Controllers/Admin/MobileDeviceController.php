<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MobileDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Exception;

class MobileDeviceController extends Controller
{
    public function index(Request $request)
    {
        // Check if this is a DataTables request
        if ($request->has('draw')) {
            return $this->getDataTablesData($request);
        }

        // Load initial view (for non-DataTables requests)
        return view('admin.mobile-device.index');
    }

    private function getDataTablesData(Request $request)
    {
        try {
            $query = MobileDevice::with('customer');
            $filteredCountQuery = MobileDevice::with('customer');

            // Get total records count
            $totalRecords = MobileDevice::count();

            // DataTables search (global search)
            $searchValue = $request->input('search.value', '');
            if (!empty($searchValue)) {
                $query->where(function($q) use ($searchValue) {
                    $q->where('device_id', 'like', "%{$searchValue}%")
                      ->orWhere('device_name', 'like', "%{$searchValue}%")
                      ->orWhere('os', 'like', "%{$searchValue}%")
                      ->orWhere('version', 'like', "%{$searchValue}%")
                      ->orWhereHas('customer', function($customerQuery) use ($searchValue) {
                          $customerQuery->where('firstname', 'like', "%{$searchValue}%")
                                        ->orWhere('lastname', 'like', "%{$searchValue}%")
                                        ->orWhere('email', 'like', "%{$searchValue}%")
                                        ->orWhere('mobile', 'like', "%{$searchValue}%");
                      });
                });

                $filteredCountQuery->where(function($q) use ($searchValue) {
                    $q->where('device_id', 'like', "%{$searchValue}%")
                      ->orWhere('device_name', 'like', "%{$searchValue}%")
                      ->orWhere('os', 'like', "%{$searchValue}%")
                      ->orWhere('version', 'like', "%{$searchValue}%")
                      ->orWhereHas('customer', function($customerQuery) use ($searchValue) {
                          $customerQuery->where('firstname', 'like', "%{$searchValue}%")
                                        ->orWhere('lastname', 'like', "%{$searchValue}%")
                                        ->orWhere('email', 'like', "%{$searchValue}%")
                                        ->orWhere('mobile', 'like', "%{$searchValue}%");
                      });
                });
            }

            // Get filtered count after search
            $filteredAfterSearch = $filteredCountQuery->count();

            // Ordering
            $orderColumnIndex = $request->input('order.0.column', 0);
            $orderDir = $request->input('order.0.dir', 'desc');

            // Default order by id
            $query->orderBy('id', $orderDir);

            // Pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 25);
            $devices = $query->skip($start)->take($length)->get();

            // Format data for DataTables
            $data = [];
            $deviceBaseUrl = url('/admin/mobiledevice');
            foreach ($devices as $device) {
                $customerName = $device->customer ? trim(($device->customer->firstname ?? '') . ' ' . ($device->customer->lastname ?? '')) : 'N/A';
                $data[] = [
                    'name' => $customerName ?: 'N/A',
                    'mobile' => $device->os ?? 'N/A',
                    'device_id' => $device->device_id ?? 'N/A',
                    'isactive' => isset($device->isactive) && $device->isactive ? 'Yes' : 'No',
                    'lastlogin' => $device->lastlogin ? \Carbon\Carbon::parse($device->lastlogin)->format('d/M/Y H:i') : 'N/A',
                    'registerdate' => $device->registerdate ? \Carbon\Carbon::parse($device->registerdate)->format('d/M/Y') : ($device->created_at ? \Carbon\Carbon::parse($device->created_at)->format('d/M/Y') : 'N/A'),
                    'action' => $device->id // For action buttons
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredAfterSearch,
                'data' => $data
            ]);
        } catch (Exception $e) {
            Log::error('MobileDevice DataTables Error: ' . $e->getMessage());
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
        $query = MobileDevice::with('customer');

        // Apply same filters as index method
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('device_id', 'like', "%{$search}%")
                  ->orWhere('device_name', 'like', "%{$search}%")
                  ->orWhere('os', 'like', "%{$search}%")
                  ->orWhere('version', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($customerQuery) use ($search) {
                      $customerQuery->where('firstname', 'like', "%{$search}%")
                                    ->orWhere('lastname', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $devices = $query->orderBy('id', 'desc')->get();

        $filename = 'mobile_devices_' . date('Y-m-d_His') . '.csv';

        $headers_array = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($devices) {
            $file = fopen('php://output', 'w');
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, ['Mobile Devices Export']);
            fputcsv($file, ['Generated on: ' . date('d-M-Y H:i:s')]);
            fputcsv($file, []);

            fputcsv($file, [
                'Device ID',
                'Customer Name',
                'Customer Email',
                'Device Name',
                'OS',
                'Version',
                'Registered Date',
                'Last Login',
                'Status'
            ]);

            foreach ($devices as $device) {
                $customerName = $device->customer ? trim(($device->customer->firstname ?? '') . ' ' . ($device->customer->lastname ?? '')) : 'N/A';
                $customerEmail = $device->customer->email ?? 'N/A';
                
                fputcsv($file, [
                    $device->device_id ?? 'N/A',
                    $customerName ?: 'N/A',
                    $customerEmail,
                    $device->device_name ?? 'N/A',
                    $device->os ?? 'N/A',
                    $device->version ?? 'N/A',
                    $device->registerdate ? \Carbon\Carbon::parse($device->registerdate)->format('d-M-Y H:i:s') : 'N/A',
                    $device->lastlogin ? \Carbon\Carbon::parse($device->lastlogin)->format('d-M-Y H:i:s') : 'N/A',
                    $device->isactive ? 'Active' : 'Inactive'
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers_array);
    }
}
