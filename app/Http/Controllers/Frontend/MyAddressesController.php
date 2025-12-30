<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class MyAddressesController extends FrontendController
{
    public function index($locale)
    {
        if (!Session::get('logged_in')) {
            return redirect()->route('home', ['locale' => $locale]);
        }

        $customerId = Session::get('customerid');
        $addresses = $this->getAddresses($customerId);

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

        $affectedRows = DB::table('addressbook')
            ->where('addressid', $addressId)
            ->where('fkcustomerid', $customerId)
            ->delete();

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

    protected function getAddresses($customerId, $addressId = 0)
    {
        $query = DB::table('addressbook as ab')
            ->select([
                'ab.*',
                'a.areaname',
                'a.areanameAR',
                'c.countryname',
                'c.countrynameAR',
                'c.countryid',
                'c.shipping_charge'
            ])
            ->leftJoin('areamaster as a', 'ab.fkareaid', '=', 'a.areaid')
            ->join('countrymaster as c', 'ab.fkcountryid', '=', 'c.countryid')
            ->where('ab.fkcustomerid', $customerId)
            ->where('c.isactive', 1)
            ->where(function($q) {
                $q->whereNull('ab.delivery_method')
                  ->orWhere('ab.delivery_method', '!=', 'Avenues Mall Delivery');
            });

        if ($addressId > 0) {
            $query->where('ab.addressid', $addressId);
            return $query->first();
        }

        return $query->get();
    }
}

