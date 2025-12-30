<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use App\Http\Controllers\Frontend\ShoppingCartController;

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
            $shoppingCartId = Session::get('shoppingcartid');
            if ($shoppingCartId) {
                try {
                    $cartItemCount = DB::table('shoppingcartitems')
                        ->where('fkcartid', $shoppingCartId)
                        ->count();
                } catch (\Exception $e) {
                    Log::warning('Could not get cart count: ' . $e->getMessage());
                    $cartItemCount = 0;
                }
            }

            // Facebook auth URL
            $authUrl = route('login.facebook', ['locale' => $locale]);

            // Ensure resourceUrl is available
            $resourceUrl = $this->resourceUrl ?? url('/resources/') . '/';

            return view('frontend.login.index', [
                'locale' => $locale,
                'cartItemCount' => $cartItemCount,
                'authUrl' => $authUrl,
                'resourceUrl' => $resourceUrl,
            ]);
        } catch (\Exception $e) {
            Log::error('LoginController index error: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            Log::error($e->getTraceAsString());
            return response('<div class="overlay-section"><div class="alert alert-danger">Error loading login form. Please try again.</div></div>', 500);
        }
    }

    /**
     * Show registration form (for overlay)
     */
    public function register()
    {
        try {
            $locale = app()->getLocale();
            
            // Facebook auth URL
            $authUrl = route('login.facebook', ['locale' => $locale]);
            
            $resourceUrl = $this->resourceUrl ?? url('/resources/') . '/';

            return view('frontend.login.register', [
                'locale' => $locale,
                'authUrl' => $authUrl,
                'resourceUrl' => $resourceUrl,
            ]);
        } catch (\Exception $e) {
            Log::error('LoginController register error: ' . $e->getMessage());
            return response('<div class="overlay-section"><div class="alert alert-danger">Error loading registration form. Please try again.</div></div>', 500);
        }
    }

    /**
     * Handle login form submission - matches CI validate()
     */
    public function validateLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6|max:25',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        // CI uses MD5, but we should check both MD5 and bcrypt for compatibility
        $customer = DB::table('customers')
            ->where('email', $email)
            ->where('isactive', 1)
            ->first();

        if (!$customer) {
            return response()->json([
                'status' => false,
                'msg' => __('Invalid login details.'),
            ]);
        }

        // Check password - CI uses MD5, but we'll support both
        $passwordValid = false;
        if ($customer->password) {
            // Try bcrypt first (for new registrations)
            if (Hash::check($password, $customer->password)) {
                $passwordValid = true;
            }
            // Try MD5 (for existing CI users)
            elseif (md5($password) === $customer->password) {
                $passwordValid = true;
                // Upgrade to bcrypt on next login
                DB::table('customers')
                    ->where('customerid', $customer->customerid)
                    ->update(['password' => Hash::make($password)]);
            }
        }

        if (!$passwordValid) {
            return response()->json([
                'status' => false,
                'msg' => __('Invalid login details.'),
            ]);
        }

        // Update last login
        DB::table('customers')
            ->where('customerid', $customer->customerid)
            ->update(['last_login' => now()]);

        // Set session data
        Session::put([
            'logged_in' => true,
            'customerid' => $customer->customerid,
            'firstname' => $customer->firstname,
            'lastname' => $customer->lastname ?? '',
            'email' => $customer->email,
            'login_type' => $customer->login_type ?? 'Register',
        ]);

        // Handle cart merge (similar to CI)
        $this->mergeCartOnLogin($customer->customerid);

        // Get wishlist count
        $wishlistCount = DB::table('wishlist')
            ->where('fkcustomerid', $customer->customerid)
            ->count();
        Session::put('wishlist_item_cnt', $wishlistCount);

        $redirect = $request->get('redirect', '');

        return response()->json([
            'status' => true,
            'msg' => __('Login successful'),
            'firstname' => $customer->firstname,
            'redirect' => $redirect,
        ]);
    }

    /**
     * Handle registration - matches CI registration()
     */
    public function registration(Request $request)
    {
        $request->validate([
            'firstname' => 'required|min:3|max:25',
            'lastname' => 'required|min:3|max:25',
            'email' => 'required|email',
            'password' => 'required|min:6|max:25',
            'confirmPassword' => 'required|same:password',
        ]);

        $email = $request->input('email');
        
        // Check if email already exists
        $existingCustomer = DB::table('customers')
            ->where('email', $email)
            ->first();

        if ($existingCustomer) {
            return response()->json([
                'status' => false,
                'msg' => __('This email already registered'),
            ]);
        }

        $locale = app()->getLocale();
        $storeId = config('app.storeid', 1);

        // Create new customer
        $customerId = DB::table('customers')->insertGetId([
            'firstname' => $request->input('firstname'),
            'lastname' => $request->input('lastname'),
            'email' => $email,
            'password' => Hash::make($request->input('password')), // Use bcrypt
            'login_type' => 'Register',
            'last_login' => now(),
            'login_ipaddress' => $request->ip(),
            'isactive' => 1,
            'language' => $locale,
            'fkstoreid' => $storeId,
            'createdon' => now(),
            'updatedby' => 0,
            'updateddate' => now(),
        ]);

        // Set session data
        Session::put([
            'logged_in' => true,
            'customerid' => $customerId,
            'firstname' => $request->input('firstname'),
            'lastname' => $request->input('lastname'),
            'email' => $email,
            'login_type' => 'Register',
        ]);

        // Handle cart merge
        $this->mergeCartOnLogin($customerId);

        // TODO: Send registration email (similar to CI)

        return response()->json([
            'status' => true,
            'msg' => __('Registration successful'),
        ]);
    }

    /**
     * Handle guest login - matches CI validate_guest()
     */
    public function validateGuest(Request $request)
    {
        $request->validate([
            'email-guest' => 'required|email',
        ]);

        $email = $request->input('email-guest');
        $locale = app()->getLocale();
        $storeId = config('app.storeid', 1);

        // Check if customer exists
        $customer = DB::table('customers')
            ->where('email', $email)
            ->first();

        if (!$customer) {
            // Create new guest customer
            $firstname = 'Guest';
            $lastname = 'User';
            
            $customerId = DB::table('customers')->insertGetId([
                'firstname' => $firstname,
                'lastname' => $lastname,
                'email' => $email,
                'password' => null,
                'login_type' => 'Guest',
                'last_login' => now(),
                'login_ipaddress' => $request->ip(),
                'isactive' => 1,
                'language' => $locale,
                'fkstoreid' => $storeId,
                'createdon' => now(),
                'updatedby' => 0,
                'updateddate' => now(),
            ]);
        } else {
            if ($customer->isactive == 0) {
                return response()->json([
                    'status' => false,
                    'msg' => __('Your account disabled. Please contact administrator.'),
                ]);
            }

            $customerId = $customer->customerid;
            $firstname = $customer->firstname;
            $lastname = $customer->lastname ?? '';

            // Update last login
            DB::table('customers')
                ->where('customerid', $customerId)
                ->update(['last_login' => now()]);
        }

        // Set session data
        Session::put([
            'logged_in' => true,
            'customerid' => $customerId,
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'login_type' => 'Guest',
        ]);

        // Handle cart merge
        $this->mergeCartOnLogin($customerId);

        // Get wishlist count
        $wishlistCount = DB::table('wishlist')
            ->where('fkcustomerid', $customerId)
            ->count();
        Session::put('wishlist_item_cnt', $wishlistCount);

        $redirect = $request->get('redirect', '');

        return response()->json([
            'status' => true,
            'msg' => __('Guest login successful'),
            'firstname' => $firstname,
            'redirect' => $redirect,
        ]);
    }

    /**
     * Redirect to Facebook - matches CI Facebook login
     */
    public function redirectToFacebook($locale)
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Handle Facebook callback - matches CI Facebook login
     */
    public function handleFacebookCallback()
    {
        try {
            $user = Socialite::driver('facebook')->user();
            
            // Get locale from session or default
            $locale = Session::get('social_login_locale', app()->getLocale());
            Session::forget('social_login_locale');
            app()->setLocale($locale);
            
            $customerData = [
                'oauth_provider' => 'Facebook',
                'oauth_uid' => $user->getId(),
                'firstname' => $user->getName() ? explode(' ', $user->getName())[0] : '',
                'lastname' => $user->getName() ? (count(explode(' ', $user->getName())) > 1 ? implode(' ', array_slice(explode(' ', $user->getName()), 1)) : '') : '',
                'email' => $user->getEmail() ?? $user->getId() . '@facebook.com',
                'gender' => $user->user['gender'] ?? null,
                'locale' => $user->user['locale'] ?? null,
                'picture_url' => $user->getAvatar() ?? null,
                'profile_url' => $user->user['link'] ?? null,
                'login_type' => 'Facebook',
                'language' => $locale,
                'isactive' => 1,
                'last_login' => now(),
                'fkstoreid' => config('app.storeid', 1),
            ];

            $customerId = $this->checkUser($customerData);

            if ($customerId) {
                Session::put([
                    'logged_in' => true,
                    'customerid' => $customerId,
                    'firstname' => $customerData['firstname'],
                    'lastname' => $customerData['lastname'],
                    'email' => $customerData['email'],
                    'login_type' => 'Facebook',
                ]);

                // Handle cart merge
                $this->mergeCartOnLogin($customerId);

                // Get wishlist count
                $wishlistCount = DB::table('wishlist')
                    ->where('fkcustomerid', $customerId)
                    ->count();
                Session::put('wishlist_item_cnt', $wishlistCount);

                return redirect('/' . $locale);
            }

            return redirect('/' . $locale)->with('error', __('Login failed'));
        } catch (\Exception $e) {
            Log::error('Facebook login error: ' . $e->getMessage());
            $locale = Session::get('social_login_locale', app()->getLocale());
            return redirect('/' . $locale)->with('error', __('Facebook login failed'));
        }
    }

    /**
     * Redirect to Google - matches CI google_login()
     */
    public function redirectToGoogle($locale = null)
    {
        // Store locale in session for callback
        if ($locale) {
            Session::put('social_login_locale', $locale);
        }
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google callback - matches CI google_login()
     */
    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();
            
            // Get locale from session or default
            $locale = Session::get('social_login_locale', app()->getLocale());
            Session::forget('social_login_locale');
            app()->setLocale($locale);
            
            $customerData = [
                'oauth_provider' => 'Google',
                'oauth_uid' => $user->getId(),
                'firstname' => $user->user['given_name'] ?? '',
                'lastname' => $user->user['family_name'] ?? '',
                'email' => $user->getEmail(),
                'gender' => $user->user['gender'] ?? null,
                'locale' => $user->user['locale'] ?? null,
                'picture_url' => $user->getAvatar() ?? null,
                'profile_url' => $user->user['link'] ?? null,
                'login_type' => 'Google',
                'language' => $locale,
                'isactive' => 1,
                'last_login' => now(),
                'fkstoreid' => config('app.storeid', 1),
            ];

            $customerId = $this->checkUser($customerData);

            if ($customerId) {
                Session::put([
                    'logged_in' => true,
                    'customerid' => $customerId,
                    'firstname' => $customerData['firstname'],
                    'lastname' => $customerData['lastname'],
                    'email' => $customerData['email'],
                    'login_type' => 'Google',
                ]);

                // Handle cart merge
                $this->mergeCartOnLogin($customerId);

                // Get wishlist count
                $wishlistCount = DB::table('wishlist')
                    ->where('fkcustomerid', $customerId)
                    ->count();
                Session::put('wishlist_item_cnt', $wishlistCount);

                return redirect('/' . $locale);
            }

            return redirect('/' . $locale)->with('error', __('Login failed'));
        } catch (\Exception $e) {
            Log::error('Google login error: ' . $e->getMessage());
            $locale = Session::get('social_login_locale', app()->getLocale());
            return redirect('/' . $locale)->with('error', __('Google login failed'));
        }
    }

    /**
     * Check or create user - matches CI checkUser()
     */
    protected function checkUser($data)
    {
        // First check by oauth_uid
        $customer = DB::table('customers')
            ->where('oauth_uid', $data['oauth_uid'])
            ->first();

        if ($customer) {
            // Update last login
            DB::table('customers')
                ->where('customerid', $customer->customerid)
                ->update(['last_login' => now()]);
            return $customer->customerid;
        }

        // Check by email if oauth_uid not found
        $customerByEmail = DB::table('customers')
            ->where('email', $data['email'])
            ->first();

        if ($customerByEmail) {
            // Update existing customer with oauth info
            DB::table('customers')
                ->where('customerid', $customerByEmail->customerid)
                ->update([
                    'oauth_provider' => $data['oauth_provider'],
                    'oauth_uid' => $data['oauth_uid'],
                    'last_login' => now(),
                    'login_type' => $data['login_type'],
                ]);
            return $customerByEmail->customerid;
        }

        // Create new customer
        $customerId = DB::table('customers')->insertGetId([
            'firstname' => $data['firstname'],
            'lastname' => $data['lastname'],
            'email' => $data['email'],
            'oauth_provider' => $data['oauth_provider'],
            'oauth_uid' => $data['oauth_uid'],
            'gender' => $data['gender'] ?? null,
            'locale' => $data['locale'] ?? null,
            'picture_url' => $data['picture_url'] ?? null,
            'profile_url' => $data['profile_url'] ?? null,
            'login_type' => $data['login_type'],
            'language' => $data['language'],
            'isactive' => $data['isactive'],
            'last_login' => $data['last_login'],
            'login_ipaddress' => request()->ip(),
            'fkstoreid' => $data['fkstoreid'],
            'createdon' => now(),
            'updatedby' => 0,
            'updateddate' => now(),
        ]);

        return $customerId;
    }

    /**
     * Merge cart on login - matches CI customer_login()
     */
    protected function mergeCartOnLogin($customerId)
    {
        $shoppingCartId = Session::get('shoppingcartid');
        $sessionId = Session::getId();

        if (!$shoppingCartId) {
            // Get or create cart for customer
            $cartController = new ShoppingCartController();
            $cartMaster = $cartController->cartMaster($customerId, '', app()->getLocale(), config('app.default_countryid', 1));
            
            if ($cartMaster && isset($cartMaster['cartid'])) {
                Session::put('shoppingcartid', $cartMaster['cartid']);
            }
            return;
        }

        // Get cart master to find session ID
        $cart = DB::table('shoppingcartmaster')
            ->where('cartid', $shoppingCartId)
            ->first();

        if ($cart && $cart->sessionid) {
            // Merge cart items from session to customer
            // Update shoppingcartitems to use customer's cart
            $customerCart = DB::table('shoppingcartmaster')
                ->where('fkcustomerid', $customerId)
                ->orderBy('cartid', 'desc')
                ->first();

            if ($customerCart) {
                // Move items to customer's cart
                DB::table('shoppingcartitems')
                    ->where('fkcartid', $shoppingCartId)
                    ->update(['fkcartid' => $customerCart->cartid]);

                // Update session cart ID
                Session::put('shoppingcartid', $customerCart->cartid);
            } else {
                // Update cart master to use customer ID
                DB::table('shoppingcartmaster')
                    ->where('cartid', $shoppingCartId)
                    ->update([
                        'fkcustomerid' => $customerId,
                        'sessionid' => '',
                    ]);
            }
        }
    }

    /**
     * Logout - matches CI logout()
     */
    public function logout()
    {
        Session::flush();
        return redirect('/' . app()->getLocale());
    }
}
