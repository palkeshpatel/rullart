<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'ordermaster';
    protected $primaryKey = 'orderid';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'fkcustomerid', 'fkstoreid', 'orderdate', 'itemtotal', 'shipping_charge',
        'total', 'fkorderstatus', 'paymentmethod', 'firstname', 'lastname',
        'mobile', 'country', 'areaname', 'address', 'currencycode', 'currencyrate',
        'addressid', 'title', 'paymentid', 'tranid', 'trackingno', 'trackingphoto',
        'firstnameBill', 'lastnameBill', 'mobileBill', 'countryBill', 'areanameBill',
        'addressBill', 'payid', 'lang', 'asGift', 'giftMessage', 'successIndicator',
        'city', 'block_number', 'house_number', 'avenue_number', 'street_number',
        'cityBill', 'block_numberBill', 'house_numberBill', 'avenue_numberBill',
        'street_numberBill', 'securityid', 'building_number', 'floor_number',
        'flat_number', 'building_numberBill', 'floor_numberBill', 'flat_numberBill',
        'fkcartid', 'mobiledevice', 'browser', 'platform', 'ismobile',
        'couponcode', 'couponvalue', 'discount', 'isread', 'shipping_method',
        'phaseid', 'additionalinstruction', 'delivery_method', 'vat_percent', 'vat',
        'phase', 'phaseAR', 'phaseidBill', 'additionalinstructionBill',
        'delivery_methodBill', 'phaseBill', 'phaseARBill', 'appversion',
        'approvalcode', 'refundpun', 'refundresponse', 'courier_company'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'fkcustomerid', 'customerid');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'fkorderid', 'orderid');
    }

    // Ensure orderid is cast correctly for relationship
    protected $casts = [
        'orderid' => 'integer',
    ];
}
