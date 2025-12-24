<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    protected $table = 'areamaster';
    protected $primaryKey = 'areaid';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'fkcountryid',
        'areaname',
        'areanameAR',
        'isactive',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class, 'fkcountryid', 'countryid');
    }
}
