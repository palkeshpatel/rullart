<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminLoginController extends Controller
{
    /**
     * Display the admin login view.
     */
    public function create(): View|\Illuminate\Http\RedirectResponse
    {
        // If already logged in as admin, redirect to dashboard
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.login');
    }

    /**
     * Handle an incoming admin authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        // Log login attempt
        Log::info('Admin Login Attempt', [
            'username' => $request->user,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $request->validate([
            'user' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Find admin by username
        $admin = Admin::where('user', $request->user)->first();
        
        // Log admin lookup result
        if ($admin) {
            Log::info('Admin User Found', [
                'id' => $admin->id,
                'username' => $admin->user,
                'email' => $admin->email,
                'password_hash_in_db' => substr($admin->pass, 0, 20) . '...',
                'lock_access' => $admin->lock_access ?? 'not set',
            ]);
        } else {
            Log::warning('Admin User Not Found', [
                'searched_username' => $request->user,
                'all_admins' => Admin::all()->pluck('user', 'id')->toArray(),
            ]);
        }
        
        // Calculate password hash
        $inputPasswordHash = md5($request->password);
        Log::info('Password Hash Comparison', [
            'input_password' => '***hidden***',
            'input_password_hash' => $inputPasswordHash,
            'stored_password_hash' => $admin ? $admin->pass : 'N/A',
            'hashes_match' => $admin ? ($inputPasswordHash === $admin->pass) : false,
        ]);
        
        // Check if admin exists and password matches (MD5 hash)
        if (!$admin) {
            Log::error('Login Failed: Admin user not found', [
                'username' => $request->user,
            ]);
            throw ValidationException::withMessages([
                'user' => __('These credentials do not match our records.'),
            ]);
        }
        
        if (md5($request->password) !== $admin->pass) {
            Log::error('Login Failed: Password mismatch', [
                'username' => $request->user,
                'input_hash' => $inputPasswordHash,
                'stored_hash' => $admin->pass,
            ]);
            throw ValidationException::withMessages([
                'user' => __('These credentials do not match our records.'),
            ]);
        }

        // Check if admin is locked
        if (isset($admin->lock_access) && $admin->lock_access == 1) {
            Log::warning('Login Failed: Account locked', [
                'username' => $request->user,
                'admin_id' => $admin->id,
            ]);
            throw ValidationException::withMessages([
                'user' => __('Your account has been locked.'),
            ]);
        }

        // Log in the admin using the 'admin' guard
        Auth::guard('admin')->login($admin, $request->boolean('remember'));
        
        Log::info('Admin Login Successful', [
            'username' => $request->user,
            'admin_id' => $admin->id,
            'remember' => $request->boolean('remember'),
        ]);

        // Regenerate session for security
        $request->session()->regenerate();

        // Redirect to dashboard
        return redirect()->route('admin.dashboard');
    }

    /**
     * Destroy an authenticated admin session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}