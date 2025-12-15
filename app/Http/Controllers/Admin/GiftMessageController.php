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

        $messages = $query->orderBy('displayorder', 'asc')->paginate(25);

        return view('admin.masters.messages', compact('messages'));
    }
}
