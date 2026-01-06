<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileDevice extends Model
{
    protected $table = 'customers_devices';
    protected $primaryKey = 'deviceid';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'deviceid',
        'fkleadform_id',
        'fkcustomerid',
        'device_uid',
        'device_name',
        'device_version',
        'device_otherdetails',
        'lastlogin',
        'registerdate',
        'update_date',
        'badge',
    ];

    public function customer()
    {
        // Try fkcustomerid first if it exists
        if (isset($this->fkcustomerid) && $this->fkcustomerid) {
            return $this->belongsTo(\App\Models\Customer::class, 'fkcustomerid', 'customerid');
        }
        // If fkleadform_id is used as customer id (fallback)
        if (isset($this->fkleadform_id) && $this->fkleadform_id) {
            return $this->belongsTo(\App\Models\Customer::class, 'fkleadform_id', 'customerid');
        }
        // Return null relationship if neither exists
        return $this->belongsTo(\App\Models\Customer::class, 'fkleadform_id', 'customerid');
    }
}
