<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SettingsController extends Controller
{
    public function index(Request $request)
    {
        $settings = Setting::orderBy('displayorder')->get();
        
        // Group settings by category if needed, or just return all
        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
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

        $settingsData = $request->except(['_token', '_method']);

        try {
            foreach ($settingsData as $name => $value) {
                $setting = Setting::where('name', $name)->first();
                
                $finalValue = $value ?? '';
                
                if ($setting) {
                    $setting->details = $finalValue;
                    $setting->save();
                } else {
                    // Create new setting if it doesn't exist
                    // Try to determine input type from value or default to text
                    $inputType = 'text';
                    if (filter_var($finalValue, FILTER_VALIDATE_EMAIL)) {
                        $inputType = 'email';
                    } elseif (filter_var($finalValue, FILTER_VALIDATE_URL)) {
                        $inputType = 'url';
                    } elseif (is_numeric($finalValue)) {
                        $inputType = 'number';
                    }
                    
                    Setting::create([
                        'name' => $name,
                        'details' => $finalValue,
                        'inputtype' => $inputType,
                        'isrequired' => 0,
                        'displayorder' => 999,
                    ]);
                }
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Settings updated successfully'
                ]);
            }

            return redirect()->route('admin.settings')
                ->with('success', 'Settings updated successfully');
        } catch (\Exception $e) {
            Log::error('Settings Update Error: ' . $e->getMessage());

            $errorMessage = 'An error occurred while updating settings.';

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $errorMessage,
                    'debug' => config('app.debug') ? $e->getMessage() : null
                ], 422);
            }

            return back()->withErrors(['error' => $errorMessage])->withInput();
        }
    }
}

