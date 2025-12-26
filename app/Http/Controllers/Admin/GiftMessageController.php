<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GiftMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

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
                'html' => view('admin.masters.partials.messages.messages-table', compact('messages'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $messages])->render(),
            ]);
        }

        return view('admin.masters.messages', compact('messages'));
    }

    public function create(Request $request)
    {
        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.messages.message-form', ['message' => null])->render(),
            ]);
        }

        return view('admin.masters.message-create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:500',
            'messageAR' => 'nullable|string|max:500',
            'displayorder' => 'nullable|integer|min:0',
            'displayorderAR' => 'nullable|integer|min:0',
            'isactive' => 'nullable',
        ], [
            'message.required' => 'Message(EN) is required.',
            'message.max' => 'Message cannot exceed 500 characters.',
        ]);

        $validated['isactive'] = $request->has('isactive') ? 1 : 0;
        $validated['displayorder'] = $validated['displayorder'] ?? 0;
        $validated['displayorderAR'] = $validated['displayorderAR'] ?? 0;

        try {
            $message = GiftMessage::create($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gift message created successfully',
                    'data' => $message
                ]);
            }

            return redirect()->route('admin.messages')
                ->with('success', 'Gift message created successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            $errorMessage = 'An error occurred while saving the gift message.';

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['message' => [$errorMessage]]
                ], 422);
            }

            return back()->withErrors(['message' => $errorMessage])->withInput();
        }
    }

    public function show(Request $request, $id)
    {
        $message = GiftMessage::findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.messages.message-view', compact('message'))->render(),
            ]);
        }

        return view('admin.masters.message-view', compact('message'));
    }

    public function edit(Request $request, $id)
    {
        $message = GiftMessage::findOrFail($id);

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.masters.partials.messages.message-form', compact('message'))->render(),
            ]);
        }

        return view('admin.masters.message-edit', compact('message'));
    }

    public function update(Request $request, $id)
    {
        $message = GiftMessage::findOrFail($id);

        // Fix for PUT requests with FormData
        if (
            $request->isMethod('put') && empty($request->all()) &&
            $request->header('Content-Type') &&
            str_contains($request->header('Content-Type'), 'multipart/form-data')
        ) {
            $content = $request->getContent();
            $boundary = null;

            if (preg_match('/boundary=([^;]+)/', $request->header('Content-Type'), $matches)) {
                $boundary = '--' . trim($matches[1]);
            }

            if ($boundary && $content) {
                $parts = explode($boundary, $content);
                $parsedData = [];

                foreach ($parts as $part) {
                    if (preg_match('/Content-Disposition:\s*form-data;\s*name="([^"]+)"\s*\r?\n\r?\n(.*?)(?=\r?\n--|$)/s', $part, $matches)) {
                        $fieldName = $matches[1];
                        $fieldValue = trim($matches[2], "\r\n");

                        if ($fieldName !== '_method') {
                            $parsedData[$fieldName] = $fieldValue;
                        }
                    }
                }

                if (!empty($parsedData)) {
                    $request->merge($parsedData);
                }
            }
        }

        $validated = $request->validate([
            'message' => 'required|string|max:500',
            'messageAR' => 'nullable|string|max:500',
            'displayorder' => 'nullable|integer|min:0',
            'displayorderAR' => 'nullable|integer|min:0',
            'isactive' => 'nullable',
        ], [
            'message.required' => 'Message(EN) is required.',
            'message.max' => 'Message cannot exceed 500 characters.',
        ]);

        $validated['isactive'] = $request->has('isactive') ? 1 : 0;

        try {
            $message->update($validated);

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Gift message updated successfully',
                    'data' => $message
                ]);
            }

            return redirect()->route('admin.messages')
                ->with('success', 'Gift message updated successfully');
        } catch (\Illuminate\Database\QueryException $e) {
            $errorMessage = 'An error occurred while updating the gift message.';

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'errors' => ['message' => [$errorMessage]]
                ], 422);
            }

            return back()->withErrors(['message' => $errorMessage])->withInput();
        }
    }

    public function destroy(Request $request, $id)
    {
        $message = GiftMessage::findOrFail($id);
        $message->delete();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Gift message deleted successfully'
            ]);
        }

        return redirect()->route('admin.messages')
            ->with('success', 'Gift message deleted successfully');
    }
}
