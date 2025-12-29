<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginController extends FrontendController
{
    /**
     * Show login form (for overlay)
     */
    public function index()
    {
        try {
            $locale = app()->getLocale();
            
            // Get cart item count to show guest login option
            $cartItemCount = 0;
            $shoppingCartId = session('shoppingcartid');
            if ($shoppingCartId) {
                try {
                    // Check if table exists before querying
                    if (DB::getSchemaBuilder()->hasTable('shoppingcartitem')) {
                        $cartItemCount = DB::table('shoppingcartitem')
                            ->where('fkshoppingcartid', $shoppingCartId)
                            ->count();
                    }
                } catch (\Exception $e) {
                    \Log::warning('Could not get cart count: ' . $e->getMessage());
                    $cartItemCount = 0;
                }
            }

            // Facebook auth URL (placeholder - implement OAuth later)
            $authUrl = '#';

            // Ensure resourceUrl is available (it should be shared via FrontendController)
            // But we'll pass it explicitly to be safe
            $resourceUrl = $this->resourceUrl ?? url('/resources/') . '/';

            return view('frontend.login.index', [
                'locale' => $locale,
                'cartItemCount' => $cartItemCount,
                'authUrl' => $authUrl,
                'resourceUrl' => $resourceUrl,
            ]);
        } catch (\Exception $e) {
            \Log::error('LoginController index error: ' . $e->getMessage());
            \Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            \Log::error($e->getTraceAsString());
            // Return a simple error message that can be displayed in the overlay
            return response('<div class="overlay-section"><div class="alert alert-danger">Error loading login form. Please try again.</div></div>', 500);
        }
    }

    /**
     * Handle login form submission
     */
    public function validateLogin(Request $request)
    {
        $email = $request->input('email');
        $password = $request->input('password');

        // Validate credentials
        $customer = DB::table('customer')
            ->where('email', $email)
            ->where('isactive', 1)
            ->first();

        if ($customer && password_verify($password, $customer->password)) {
            // Set session data
            session([
                'logged_in' => true,
                'customerid' => $customer->customerid,
                'firstname' => $customer->firstname,
                'lastname' => $customer->lastname,
                'email' => $customer->email,
            ]);

            // Check if there's a redirect parameter (e.g., from checkout)
            $redirect = request()->get('redirect', '');

            return response()->json([
                'status' => true,
                'msg' => __('Login successful'),
                'firstname' => $customer->firstname,
                'redirect' => $redirect,
            ]);
        }

        return response()->json([
            'status' => false,
            'msg' => __('Invalid email or password'),
        ]);
    }

    /**
     * Handle guest login (for checkout)
     */
    public function guestLogin(Request $request)
    {
        $email = $request->input('email-guest', '');

        // For guest login, we just set a minimal session
        session([
            'logged_in' => false,
            'guest_email' => $email,
        ]);

        return response()->json([
            'status' => true,
            'msg' => __('Guest login successful'),
            'redirect' => 'shoppingcart',
        ]);
    }

    /**
     * Handle guest login validation (alternative endpoint)
     */
    public function validateGuest(Request $request)
    {
        return $this->guestLogin($request);
    }
}

