<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminLoginController extends Controller
{
    /**
     * Display the admin login view.
     */
    public function create(): View
    {
        return view('admin.login');
    }

    /**
     * Handle an incoming admin authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'user' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Find admin by username
        $admin = Admin::where('user', $request->user)->first();

        // Check if admin exists and password matches (MD5 hash)
        if (!$admin || md5($request->password) !== $admin->pass) {
            throw ValidationException::withMessages([
                'user' => __('These credentials do not match our records.'),
            ]);
        }

        // Check if admin is locked
        if (isset($admin->lock_access) && $admin->lock_access == 1) {
            throw ValidationException::withMessages([
                'user' => __('Your account has been locked.'),
            ]);
        }

        // Log in the admin using the 'admin' guard
        Auth::guard('admin')->login($admin, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
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

