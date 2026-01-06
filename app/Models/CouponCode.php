<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CouponCode extends Model
{
    protected $table = 'couponcode';
    protected $primaryKey = 'couponcodeid';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'couponcode',
        'couponvalue',
        'isactive',
        'isgeneral',
        'fkcoupontypeid',
        'startdate',
        'enddate',
        'ismultiuse',
        'coupontype',
        'fkcategoryid',
    ];
}
