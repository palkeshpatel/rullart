<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('category', 'like', "%{$search}%")
                  ->orWhere('categoryAR', 'like', "%{$search}%")
                  ->orWhere('categorycode', 'like', "%{$search}%");
            });
        }

        $categories = $query->orderBy('displayorder', 'asc')->paginate(25);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.partials.categories-table', compact('categories'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $categories])->render(),
            ]);
        }

        return view('admin.category', compact('categories'));
    }
}
