<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MobileDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class MobileDeviceController extends Controller
{
    public function index(Request $request)
    {
        $query = MobileDevice::with('customer');

        // Search functionality
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
                                    ->orWhere('email', 'like', "%{$search}%")
                                    ->orWhere('mobile', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = $request->get('per_page', 25);
        $devices = $query->orderBy('id', 'desc')->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.mobile-device.partials.table', compact('devices'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $devices])->render(),
            ]);
        }

        return view('admin.mobile-device.index', compact('devices'));
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
