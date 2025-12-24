<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function index(Request $request)
    {
        $query = Area::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('areaname', 'like', "%{$search}%")
                  ->orWhere('areanameAR', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortColumn = $request->get('sort', 'areaid');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortColumn, $sortDirection);

        $perPage = $request->get('per_page', 25);
        $areas = $query->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.areas-table', compact('areas'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $areas])->render(),
            ]);
        }

        return view('admin.masters.areas', compact('areas'));
    }
}
