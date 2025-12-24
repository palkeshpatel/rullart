<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingCartMaster extends Model
{
    protected $table = 'shoppingcartmaster';
    protected $primaryKey = 'cartid';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'fkcustomerid',
        'fkstoreid',
        'sessionid',
        'orderdate',
        'itemtotal',
        'shipping_charge',
        'giftbox_charge',
        'total',
        'paymentmethod',
        'addressid',
        'lang',
        'asGift',
        'giftMessage',
        'billingaddressid',
        'shippingaddressid',
        'couponcode',
        'couponvalue',
        'discount',
        'shippingcountryid',
        'shipping_method',
        'phaseid',
        'additionalinstruction',
        'phase',
        'phaseAR',
        'phaseidBill',
        'additionalinstructionBill',
        'delivery_methodBill',
        'phaseBill',
        'phaseARBill',
        'delivery_method',
        'vat_percent',
        'vat',
        'free_shipping_over',
        'free_shipping_text',
        'ismobile',
        'mobiledevice',
        'browser',
        'platform',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'fkcustomerid', 'customerid');
    }

    public function addressbook()
    {
        return $this->belongsTo(AddressBook::class, 'addressid', 'addressid');
    }

    public function items()
    {
        return $this->hasMany(ShoppingCartItem::class, 'fkcartid', 'cartid');
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'fkcartid', 'cartid');
    }
}

