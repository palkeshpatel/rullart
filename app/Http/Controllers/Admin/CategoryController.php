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

        // Filter by parent category
        if ($request->filled('parent_category') && $request->parent_category !== '' && $request->parent_category !== '--Parent--') {
            if ($request->parent_category == '0') {
                $query->where('parentid', 0)->orWhereNull('parentid');
            } else {
                $query->where('parentid', $request->parent_category);
            }
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('category', 'like', "%{$search}%")
                  ->orWhere('categoryAR', 'like', "%{$search}%")
                  ->orWhere('categorycode', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 25);
        $categories = $query->orderBy('displayorder', 'asc')->paginate($perPage);

        // Get parent categories for dropdown
        $parentCategories = Category::where('parentid', 0)->orWhereNull('parentid')->orderBy('category')->get();

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.partials.categories-table', compact('categories'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $categories])->render(),
            ]);
        }

        return view('admin.category.index', compact('categories', 'parentCategories'));
    }

    public function show(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.category.partials.modal', compact('category'))->render(),
            ]);
        }

        return view('admin.category.show', compact('category'));
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return view('admin.category.edit', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'category' => 'required|string|max:255',
            'categoryAR' => 'nullable|string|max:255',
            'categorycode' => 'required|string|max:255',
            'ispublished' => 'nullable',
            'showmenu' => 'nullable',
            'displayorder' => 'nullable|integer',
        ]);

        // Handle boolean fields
        $validated['ispublished'] = $request->has('ispublished') ? 1 : 0;
        $validated['showmenu'] = $request->has('showmenu') ? 1 : 0;

        $category->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully'
            ]);
        }

        return redirect()->route('admin.category.edit', $id)
            ->with('success', 'Category updated successfully');
    }
}
