<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Size;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    public function index(Request $request)
    {
        $query = Size::sizes(); // Use scope to filter by commonname = 'size'

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('commonvalue', 'like', "%{$search}%")
                  ->orWhere('commonvalueAR', 'like', "%{$search}%");
            });
        }

        $sizes = $query->orderBy('displayorder', 'asc')->paginate(25);

        return view('admin.masters.sizes', compact('sizes'));
    }
}
