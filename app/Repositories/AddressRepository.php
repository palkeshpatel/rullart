<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class AddressRepository
{
    /**
     * Get customer addresses
     * Matches CI Addressbook_model->getdata()
     */
    public function getCustomerAddresses($customerId)
    {
        return DB::table('addressbook as ab')
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
            ->where(function ($q) {
                $q->whereNull('ab.delivery_method')
                    ->orWhere('ab.delivery_method', '!=', 'Avenues Mall Delivery');
            })
            ->orderBy('ab.is_default', 'desc')
            ->orderBy('ab.addressid', 'desc')
            ->get();
    }

    /**
     * Get areas for address form
     * Matches CI Areas_model->get_all()
     */
    public function getAreas()
    {
        return DB::table('areamaster')
            ->where('isactive', 1)
            ->orderBy('areaname', 'asc')
            ->get();
    }

    /**
     * Get areas by country
     * Matches CI Areas_model->get_all($country)
     */
    public function getAreasByCountry($countryName)
    {
        return DB::table('areamaster as a')
            ->join('countrymaster as c', 'a.fkcountryid', '=', 'c.countryid')
            ->where('c.countryname', $countryName)
            ->where('a.isactive', 1)
            ->where('c.isactive', 1)
            ->select('a.*')
            ->orderBy('a.areaname', 'asc')
            ->get();
    }

    /**
     * Get address by ID
     */
    public function getAddressById($addressId)
    {
        return DB::table('addressbook')
            ->where('addressid', $addressId)
            ->first();
    }

    /**
     * Get customer addresses from addressbook
     */
    public function getAddressesByCustomer($customerId, $locale)
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
            ->where(function ($q) {
                $q->whereNull('ab.delivery_method')
                    ->orWhere('ab.delivery_method', '!=', 'Avenues Mall Delivery');
            })
            ->orderBy('ab.is_default', 'desc')
            ->orderBy('ab.addressid', 'desc');

        return $query->get();
    }

    /**
     * Delete address
     */
    public function deleteAddress($addressId, $customerId)
    {
        return DB::table('addressbook')
            ->where('addressid', $addressId)
            ->where('fkcustomerid', $customerId)
            ->update(['isactive' => 0]);
    }
}
