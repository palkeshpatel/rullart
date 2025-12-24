<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    public function index(Request $request)
    {
        $query = Color::colors(); // Use scope to filter by fkfilterid = 2

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
        $colors = $query->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.colors-table', compact('colors'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $colors])->render(),
            ]);
        }

        return view('admin.masters.colors', compact('colors'));
    }
}
