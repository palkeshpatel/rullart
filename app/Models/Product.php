<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'productid';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'fkcategoryid', 'title', 'titleAR', 'productcode', 'shortdescr',
        'price', 'discount', 'sellingprice', 'ispublished', 'isnew', 'ispopular'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'fkcategoryid', 'categoryid');
    }

    public function ratings()
    {
        return $this->hasMany(ProductRating::class, 'fkproductid', 'productid');
    }
}
