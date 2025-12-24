<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    public function index(Request $request)
    {
        $query = Size::sizes(); // Use scope to filter by fkfilterid = 3

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('filtervalue', 'like', "%{$search}%")
                  ->orWhere('filtervalueAR', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortColumn = $request->get('sort', 'displayorder');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortColumn, $sortDirection);

        $perPage = $request->get('per_page', 25);
        $sizes = $query->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.sizes.sizes-table', compact('sizes'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $sizes])->render(),
            ]);
        }

        return view('admin.masters.sizes', compact('sizes'));
    }
}
