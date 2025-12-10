<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductRating extends Model
{
    protected $table = 'productrating';
    protected $primaryKey = 'rateid';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'fkproductid', 'rate', 'review', 'fkorderid',
        'fkcustomerid', 'submiton', 'ispublished'
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
