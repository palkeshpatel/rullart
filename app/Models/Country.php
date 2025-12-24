<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countrymaster';
    protected $primaryKey = 'countryid';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'countryname',
        'countrynameAR',
        'isocode',
        'currencycode',
        'currencyrate',
        'isactive',
        'shipping_charge',
        'shipping_days',
        'shipping_daysAR',
        'currencysymbol',
        'free_shipping_over',
    ];
}