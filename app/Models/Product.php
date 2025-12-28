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
        'fkcategoryid', 'title', 'titleAR', 'productcode', 'shortdescr', 'shortdescrAR',
        'longdescr', 'longdescrAR', 'price', 'discount', 'sellingprice', 
        'ispublished', 'isnew', 'ispopular', 'photo1', 'photo2', 'photo3', 'photo4', 'photo5',
        'metakeyword', 'metadescr', 'metatitle', 'isgift', 'updatedby', 'updateddate'
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
