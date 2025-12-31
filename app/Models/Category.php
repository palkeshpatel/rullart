<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'category';
    protected $primaryKey = 'categoryid';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'category', 'categoryAR', 'categorycode', 'ispublished',
        'showmenu', 'displayorder', 'parentid',
        'metakeyword', 'metadescr', 'metatitle',
        'metakeywordAR', 'metadescrAR', 'metatitleAR',
        'photo', 'photo_mobile', 'updatedby', 'updateddate'
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'fkcategoryid', 'categoryid');
    }
}
