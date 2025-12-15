<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wishlist extends Model
{
    protected $table = 'wishlist';
    protected $primaryKey = 'wishlistid';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'fkproductid',
        'fkcustomerid',
        'createdon',
        'emailsenddate',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'fkproductid', 'productid');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'fkcustomerid', 'customerid');
    }
}
