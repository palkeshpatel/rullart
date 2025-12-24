<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftMessage;
use Illuminate\Http\Request;

class GiftMessageController extends Controller
{
    public function index(Request $request)
    {
        $query = GiftMessage::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                  ->orWhere('messageAR', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->has('active') && $request->active !== '') {
            $query->where('isactive', $request->active);
        }

        // Sorting
        $sortColumn = $request->get('sort', 'displayorder');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortColumn, $sortDirection);

        $perPage = $request->get('per_page', 25);
        $messages = $query->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.messages-table', compact('messages'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $messages])->render(),
            ]);
        }

        return view('admin.masters.messages', compact('messages'));
    }
}
