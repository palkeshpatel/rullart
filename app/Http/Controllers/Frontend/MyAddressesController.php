<?php

namespace App\Http\Controllers\Frontend;

use App\Repositories\AddressRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class MyAddressesController extends FrontendController
{
    protected $addressRepository;

    public function __construct(AddressRepository $addressRepository)
    {
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
}

