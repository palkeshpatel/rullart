<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function index(Request $request)
    {
        $query = Area::areas(); // Use scope to filter by commonname = 'area'

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('commonvalue', 'like', "%{$search}%")
                  ->orWhere('commonvalueAR', 'like', "%{$search}%");
            });
        }

        $areas = $query->orderBy('displayorder', 'asc')->paginate(25);

        return view('admin.masters.areas', compact('areas'));
    }
}
