<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    public function index(Request $request)
    {
        $query = Color::colors(); // Use scope to filter by commonname = 'color'

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('commonvalue', 'like', "%{$search}%")
                  ->orWhere('commonvalueAR', 'like', "%{$search}%");
            });
        }

        $colors = $query->orderBy('displayorder', 'asc')->paginate(25);

        return view('admin.masters.colors', compact('colors'));
    }
}
