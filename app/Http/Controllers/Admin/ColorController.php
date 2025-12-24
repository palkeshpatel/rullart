<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Color;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    public function index(Request $request)
    {
        $query = Color::colors(); // Use scope to filter by fkfilterid = 2

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('filtervalue', 'like', "%{$search}%")
                  ->orWhere('filtervalueAR', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortColumn = $request->get('sort', 'displayorder');
        $sortDirection = $request->get('direction', 'asc');
        $query->orderBy($sortColumn, $sortDirection);

        $perPage = $request->get('per_page', 25);
        $colors = $query->paginate($perPage);

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.colors-table', compact('colors'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $colors])->render(),
            ]);
        }

        return view('admin.masters.colors', compact('colors'));
    }

    public function create(Request $request)
    {
        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.color-form', ['color' => null])->render(),
            ]);
        }

        return view('admin.masters.color-create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'filtervalue' => 'required|string|max:255',
            'filtervalueAR' => 'nullable|string|max:255',
            'filtervaluecode' => 'nullable|string|max:255',
            'isactive' => 'nullable',
            'displayorder' => 'nullable|integer',
        ]);

        $validated['fkfilterid'] = 2; // Color filter ID
        $validated['isactive'] = $request->has('isactive') ? 1 : 0;
        $validated['displayorder'] = $validated['displayorder'] ?? 0;

        $color = Color::create($validated);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Color created successfully',
                'data' => $color
            ]);
        }

        return redirect()->route('admin.colors')
            ->with('success', 'Color created successfully');
    }

    public function show(Request $request, $id)
    {
        $color = Color::colors()->findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.color-view', compact('color'))->render(),
            ]);
        }

        return view('admin.masters.color-view', compact('color'));
    }

    public function edit(Request $request, $id)
    {
        $color = Color::colors()->findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.color-form', compact('color'))->render(),
            ]);
        }

        return view('admin.masters.color-edit', compact('color'));
    }

    public function update(Request $request, $id)
    {
        $color = Color::colors()->findOrFail($id);

        $validated = $request->validate([
            'filtervalue' => 'required|string|max:255',
            'filtervalueAR' => 'nullable|string|max:255',
            'filtervaluecode' => 'nullable|string|max:255',
            'isactive' => 'nullable',
            'displayorder' => 'nullable|integer',
        ]);

        $validated['isactive'] = $request->has('isactive') ? 1 : 0;
        $validated['displayorder'] = $validated['displayorder'] ?? 0;

        $color->update($validated);

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Color updated successfully',
                'data' => $color
            ]);
        }

        return redirect()->route('admin.colors')
            ->with('success', 'Color updated successfully');
    }

    public function destroy(Request $request, $id)
    {
        $color = Color::colors()->findOrFail($id);
        $color->delete();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Color deleted successfully'
            ]);
        }

        return redirect()->route('admin.colors')
            ->with('success', 'Color deleted successfully');
    }
}
