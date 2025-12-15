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
        'country',
        'countryAR',
        'countrycode',
        'currencycode',
        'currencyrate',
        'ispublished',
    ];
}
