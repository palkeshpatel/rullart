<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'ordermaster';
    protected $primaryKey = 'orderid';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'fkcustomerid', 'fkstoreid', 'orderdate', 'itemtotal', 'shipping_charge',
        'total', 'fkorderstatus', 'paymentmethod', 'firstname', 'lastname',
        'mobile', 'country', 'areaname', 'address', 'currencycode', 'currencyrate'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'fkcustomerid', 'customerid');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'fkorderid', 'orderid');
    }
}
