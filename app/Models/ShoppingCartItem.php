<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingCartItem extends Model
{
    protected $table = 'shoppingcartitems';
    protected $primaryKey = 'cartitemid';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'fkcartid',
        'fkproductid',
        'size',
        'qty',
        'price',
        'actualprice',
        'subtotal',
    ];

    public function cart()
    {
        return $this->belongsTo(ShoppingCartMaster::class, 'fkcartid', 'cartid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'fkproductid', 'productid');
    }
}

