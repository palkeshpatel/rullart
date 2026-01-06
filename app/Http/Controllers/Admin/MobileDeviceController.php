<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MobileDevice;
use App\Models\Category;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
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
        $categories = Category::where('ispublished', 1)
            ->whereNull('parentid')
            ->orderBy('displayorder', 'asc')
            ->get(['categoryid', 'category', 'categorycode']);

        return view('admin.mobile-device.index', compact('categories'));
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
                $query->where(function ($q) use ($searchValue) {
                    $q->where('device_uid', 'like', "%{$searchValue}%")
                        ->orWhere('device_name', 'like', "%{$searchValue}%")
                        ->orWhere('os', 'like', "%{$searchValue}%")
                        ->orWhere('version', 'like', "%{$searchValue}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($searchValue) {
                            $customerQuery->where('firstname', 'like', "%{$searchValue}%")
                                ->orWhere('lastname', 'like', "%{$searchValue}%")
                                ->orWhere('email', 'like', "%{$searchValue}%")
                                ->orWhere('mobile', 'like', "%{$searchValue}%");
                        });
                });

                $filteredCountQuery->where(function ($q) use ($searchValue) {
                    $q->where('device_uid', 'like', "%{$searchValue}%")
                        ->orWhere('device_name', 'like', "%{$searchValue}%")
                        ->orWhere('os', 'like', "%{$searchValue}%")
                        ->orWhere('version', 'like', "%{$searchValue}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($searchValue) {
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

            // Default order by deviceid
            $query->orderBy('deviceid', $orderDir);

            // Pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 25);
            $devices = $query->skip($start)->take($length)->get();

            // Format data for DataTables
            $data = [];
            $deviceBaseUrl = url('/admin/mobiledevice');
            foreach ($devices as $device) {
                $customerName = $device->customer ? trim(($device->customer->firstname ?? '') . ' ' . ($device->customer->lastname ?? '')) : 'N/A';
                $deviceId = $device->device_uid ?? 'N/A';
                $data[] = [
                    'name' => $customerName ?: 'N/A',
                    'mobile' => $device->customer ? ($device->customer->mobile ?? 'N/A') : 'N/A',
                    'device_id' => $deviceId,
                    'isactive' => 'Yes', // customers_devices table doesn't have isactive column, default to Yes
                    'lastlogin' => $device->lastlogin ? \Carbon\Carbon::parse($device->lastlogin)->format('d/M/Y H:i') : 'N/A',
                    'registerdate' => $device->registerdate ? \Carbon\Carbon::parse($device->registerdate)->format('d/M/Y') : ($device->update_date ? \Carbon\Carbon::parse($device->update_date)->format('d/M/Y') : 'N/A'),
                    'action' => $device->deviceid // For action buttons
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
            $query->where(function ($q) use ($search) {
                $q->where('device_uid', 'like', "%{$search}%")
                    ->orWhere('device_name', 'like', "%{$search}%")
                    ->orWhere('os', 'like', "%{$search}%")
                    ->orWhere('version', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $devices = $query->orderBy('deviceid', 'desc')->get();

        $filename = 'mobile_devices_' . date('Y-m-d_His') . '.csv';

        $headers_array = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($devices) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

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
                    $device->device_uid ?? 'N/A',
                    $customerName ?: 'N/A',
                    $customerEmail,
                    $device->device_name ?? 'N/A',
                    $device->os ?? 'N/A',
                    $device->version ?? 'N/A',
                    $device->registerdate ? \Carbon\Carbon::parse($device->registerdate)->format('d-M-Y H:i:s') : 'N/A',
                    $device->lastlogin ? \Carbon\Carbon::parse($device->lastlogin)->format('d-M-Y H:i:s') : 'N/A',
                    'Active' // customers_devices table doesn't have isactive column
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers_array);
    }

    public function getDevices(Request $request)
    {
        try {
            $devices = MobileDevice::select('deviceid', 'device_uid', 'fkcustomerid')
                ->whereNotNull('device_uid')
                ->get();

            $deviceList = [];
            foreach ($devices as $device) {
                if ($device->device_uid) {
                    $deviceList[] = [
                        'id' => $device->deviceid,
                        'device_id' => $device->device_uid
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'devices' => $deviceList
            ]);
        } catch (Exception $e) {
            Log::error('Get Devices Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load devices.'
            ], 500);
        }
    }

    public function sendNotification(Request $request)
    {
        try {
            $request->validate([
                'device_id' => 'nullable|string',
                'device_ids' => 'nullable|array',
                'redirect_type' => 'required|string',
                'category_id' => 'nullable|integer',
                'title' => 'required|string|max:500',
                'message' => 'required|string|max:5000'
            ]);

            $deviceId = $request->input('device_id');
            $deviceIds = $request->input('device_ids', []);
            $redirectType = $request->input('redirect_type');
            $categoryId = $request->input('category_id');
            $title = $request->input('title');
            $message = $request->input('message');
            $adminId = auth()->guard('admin')->id() ?? 1;

            // Get redirect code based on redirect type
            $redirectCode = null;
            if ($redirectType === 'category' && $categoryId) {
                $category = Category::find($categoryId);
                $redirectCode = $category ? $category->categorycode : null;
            }

            // Get devices to send notification to
            $devices = [];

            // If device_ids array is provided (for selected devices)
            if (!empty($deviceIds) && is_array($deviceIds)) {
                if (in_array('All', $deviceIds) || (count($deviceIds) === 1 && $deviceIds[0] === 'All')) {
                    // Send to all devices (customers_devices table only has device_uid, not device_id)
                    $devices = MobileDevice::whereNotNull('device_uid')->get();
                } else {
                    // Send to selected devices - deviceIds could be device_uid strings or deviceid integers
                    // Separate numeric IDs from device_uid strings
                    $numericIds = array_filter($deviceIds, function ($id) {
                        return is_numeric($id) && $id != 'All';
                    });
                    $uidStrings = array_filter($deviceIds, function ($id) {
                        return !is_numeric($id) && $id != 'All';
                    });

                    $devices = collect();
                    if (!empty($numericIds)) {
                        $devices = $devices->merge(MobileDevice::whereIn('deviceid', $numericIds)->get());
                    }
                    if (!empty($uidStrings)) {
                        $devices = $devices->merge(MobileDevice::whereIn('device_uid', $uidStrings)->get());
                    }
                    $devices = $devices->unique('deviceid');
                }
            } elseif ($deviceId && $deviceId !== 'All') {
                // Single device by device_uid (customers_devices table only has device_uid)
                $device = MobileDevice::where('device_uid', $deviceId)->first();
                if ($device) {
                    $devices[] = $device;
                }
            } else {
                // Send to all devices
                $devices = MobileDevice::whereNotNull('device_uid')->get();
            }

            if (empty($devices)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No devices found to send notification.'
                ], 400);
            }

            // Get starting notification ID (do this once before the loop)
            $maxId = DB::table('notifications')->max('notificationid');
            $nextId = ($maxId ? (int)$maxId : 0) + 1;

            $sentCount = 0;
            $errors = [];

            foreach ($devices as $device) {
                try {
                    $deviceUid = $device->device_uid ?? null;
                    if ($deviceUid) {
                        $notificationData = [
                            'notificationid' => (int)$nextId,
                            'fkcustomerid' => $device->fkcustomerid ? (int)$device->fkcustomerid : null,
                            'device_uid' => (string)$deviceUid,
                            'title' => (string)$title,
                            'message' => (string)$message,
                            'isread' => 0,
                            'createdby' => (int)$adminId,
                            'createdon' => date('Y-m-d'),
                            'redirect_type' => (string)$redirectType,
                            'redirect_code' => $redirectCode ? (string)$redirectCode : null,
                            'badge' => 1
                        ];

                        Notification::create($notificationData);
                        $nextId++; // Increment for next notification
                        $sentCount++;
                    }
                } catch (\Exception $e) {
                    Log::error('Notification creation error for device ' . ($device->deviceid ?? 'unknown') . ': ' . $e->getMessage());
                    $errors[] = $e->getMessage();
                }
            }

            if ($sentCount === 0 && !empty($errors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send notification. ' . implode(' ', array_slice($errors, 0, 3))
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => "Notification sent successfully to {$sentCount} device(s)."
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Send Notification Error: ' . $e->getMessage());
            Log::error('Send Notification Error Trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage()
            ], 500);
        }
    }
}
