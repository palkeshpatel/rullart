<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Occassion;
use Illuminate\Http\Request;

class OccassionController extends Controller
{
    public function index(Request $request)
    {
        $query = Occassion::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('occassion', 'like', "%{$search}%")
                  ->orWhere('occassionAR', 'like', "%{$search}%")
                  ->orWhere('occassioncode', 'like', "%{$search}%");
            });
        }

        $occassions = $query->orderBy('occassionid', 'desc')->paginate(25);

        return view('admin.occassion', compact('occassions'));
    }
}
