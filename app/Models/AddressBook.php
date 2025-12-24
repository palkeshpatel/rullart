<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddressBook extends Model
{
    protected $table = 'addressbook';
    protected $primaryKey = 'addressid';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'fkcustomerid',
        'title',
        'firstname',
        'lastname',
        'mobile',
        'country',
        'fkareaid',
        'address',
        'securityid',
        'city',
        'block_number',
        'house_number',
        'avenue_number',
        'street_number',
        'building_number',
        'floor_number',
        'flat_number',
        'fkcountryid',
        'is_default',
        'phaseid',
        'phase',
        'phaseAR',
        'additionalinstruction',
        'delivery_method',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'fkcustomerid', 'customerid');
    }
}

