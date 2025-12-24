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

        $perPage = $request->get('per_page', 25);
        $occassions = $query->orderBy('occassionid', 'desc')->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.partials.occassions-table', compact('occassions'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $occassions])->render(),
            ]);
        }

        return view('admin.occassion', compact('occassions'));
    }
}
