<?php

namespace App\Http\Controllers\Frontend;

use App\Repositories\AddressRepository;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class MyAddressesController extends FrontendController
{
    protected $addressRepository;

    public function __construct(AddressRepository $addressRepository)
    {
        parent::__construct();
        $this->addressRepository = $addressRepository;
    }

    public function index($locale)
    {
        if (!Session::get('logged_in')) {
            return redirect()->route('home', ['locale' => $locale]);
        }

        $customerId = Session::get('customerid');
        $addresses = $this->addressRepository->getAddressesByCustomer($customerId, $locale);

        $data = [
            'locale' => $locale,
            'addresses' => $addresses,
        ];

        return view('frontend.myaddresses.index', $data);
    }

    public function remove($locale, Request $request)
    {
        if (!Session::get('logged_in')) {
            return response()->json(['status' => false, 'msg' => __('Unauthorized')], 401);
        }

        $addressId = $request->input('addressid');
        $customerId = Session::get('customerid');

        $affectedRows = $this->addressRepository->deleteAddress($addressId, $customerId);

        if ($affectedRows == 0) {
            return response()->json([
                'status' => false,
                'msg' => __('Error in removed!!!')
            ]);
        }

        return response()->json([
            'status' => true,
            'msg' => __('Address removed successfully!!!')
        ]);
    }

    public function add($locale)
    {
        if (!Session::get('logged_in')) {
            return redirect()->route('home', ['locale' => $locale]);
        }

        // Get countries
        $countries = Country::where('isactive', 1)->get();

        // Get areas for default country
        $defaultCountry = config('app.default_country', 'Kuwait');
        $areas = $this->addressRepository->getAreasByCountry($defaultCountry);

        $data = [
            'locale' => $locale,
            'countries' => $countries,
            'areas' => $areas,
        ];

        return view('frontend.myaddresses.add', $data);
    }

    public function save($locale, Request $request)
    {
        if (!Session::get('logged_in')) {
            return response()->json([
                'status' => false,
                'msg' => __('Unauthorized')
            ], 401);
        }

        $rules = [
            'firstname' => 'required|min:3|max:25',
            'lastname' => 'required|min:3|max:25',
            'mobile' => 'required|min:3|max:25',
            'addressTitle' => 'required|min:3|max:25',
            'country' => 'required',
            'block_number' => 'required',
            'street_number' => 'required',
            'house_number' => 'required',
        ];

        // Area is required for Kuwait, city for other countries
        if ($request->input('country') == 'Kuwait') {
            $rules['area'] = 'required';
        } else {
            $rules['city'] = 'required';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'msg' => $validator->errors()->first()
            ], 422);
        }

        $customerId = Session::get('customerid');
        $addressId = $this->addressRepository->saveAddress($customerId, $request->all());

        if (!$addressId) {
            return response()->json([
                'status' => false,
                'msg' => __('Error saving address')
            ], 500);
        }

        return response()->json([
            'status' => true,
            'msg' => __('Address saved successfully'),
            'redirect' => route('myaddresses', ['locale' => $locale])
        ]);
    }
}

