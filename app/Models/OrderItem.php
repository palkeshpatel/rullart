<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'orderitems';
    protected $primaryKey = 'orderitemid';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'fkorderid', 'fkproductid', 'title', 'qty', 'price',
        'actualprice', 'size', 'discount', 'subtotal', 'photo'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'fkorderid', 'orderid');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'fkproductid', 'productid');
    }
}
