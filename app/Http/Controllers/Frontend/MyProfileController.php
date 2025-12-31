<?php

namespace App\Http\Controllers\Frontend;

use App\Repositories\CustomerRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MyProfileController extends FrontendController
{
    protected $customerRepository;

    public function __construct(CustomerRepository $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    public function index($locale)
    {
        if (!Session::get('logged_in')) {
            return redirect()->route('home', ['locale' => $locale]);
        }

        return view('frontend.myprofile.index', ['locale' => $locale]);
    }

    public function profileUpdate($locale, Request $request)
    {
        if (!Session::get('logged_in')) {
            return response()->json(['status' => false, 'msg' => __('Unauthorized')], 401);
        }

        $validator = Validator::make($request->all(), [
            'firstname' => 'required|min:3|max:25',
            'lastname' => 'required|min:3|max:25',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first()
            ]);
        }

        $customerId = Session::get('customerid');

        $this->customerRepository->updateCustomer($customerId, [
            'firstname' => $request->input('firstname'),
            'lastname' => $request->input('lastname'),
        ]);

        // Update session
        Session::put('firstname', $request->input('firstname'));
        Session::put('lastname', $request->input('lastname'));

        return response()->json(['status' => true]);
    }

    public function changePassword($locale, Request $request)
    {
        if (!Session::get('logged_in')) {
            return response()->json(['status' => false, 'msg' => __('Unauthorized')], 401);
        }

        $validator = Validator::make($request->all(), [
            'currentPassword' => 'required|min:6|max:25',
            'newPassword' => 'required|min:6|max:25',
            'confirmNewPassword' => 'required|min:6|max:25|same:newPassword',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first()
            ]);
        }

        $customerId = Session::get('customerid');
        $email = Session::get('email');
        $currentPassword = $request->input('currentPassword');

        // Get customer
        $customer = $this->customerRepository->getCustomerById($customerId);
        
        if (!$customer || $customer->email !== $email) {
            return response()->json([
                'status' => false,
                'msg' => __('Invalid current password.')
            ]);
        }

        // Check current password (support both MD5 and bcrypt)
        $passwordValid = false;
        if ($customer->password) {
            if (Hash::check($currentPassword, $customer->password)) {
                $passwordValid = true;
            } elseif (md5($currentPassword) === $customer->password) {
                $passwordValid = true;
            }
        }

        if (!$passwordValid) {
            return response()->json([
                'status' => false,
                'msg' => __('Invalid current password.')
            ]);
        }

        // Update password
        $this->customerRepository->updatePassword($customerId, $request->input('newPassword'));

        return response()->json(['status' => true]);
    }
}

