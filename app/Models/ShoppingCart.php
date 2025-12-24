<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingCart extends Model
{
    protected $table = 'shoppingcart';
    protected $primaryKey = 'shoppingcartid';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'fkcustomerid',
        'sessionid',
        'fkaddressbookid',
        'totalqty',
        'totalamt',
        'fkstatusid',
        'updatedon',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'fkcustomerid', 'customerid');
    }

    public function addressbook()
    {
        return $this->belongsTo(AddressBook::class, 'fkaddressbookid', 'addressid');
    }
}
