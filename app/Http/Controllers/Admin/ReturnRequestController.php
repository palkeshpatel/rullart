<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReturnRequest;
use Illuminate\Http\Request;

class ReturnRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = ReturnRequest::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('orderno', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 25);
        $returnRequests = $query->orderBy('submiton', 'desc')->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.return-request.partials.table', compact('returnRequests'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $returnRequests])->render(),
            ]);
        }

        return view('admin.return-request.index', compact('returnRequests'));
    }
}
