<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MobileDevice;
use Illuminate\Http\Request;

class MobileDeviceController extends Controller
{
    public function index(Request $request)
    {
        $query = MobileDevice::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('device_id', 'like', "%{$search}%")
                  ->orWhere('device_name', 'like', "%{$search}%")
                  ->orWhere('os', 'like', "%{$search}%")
                  ->orWhere('version', 'like', "%{$search}%");
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
}
