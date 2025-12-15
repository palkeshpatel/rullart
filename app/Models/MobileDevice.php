<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MobileDevice extends Model
{
    protected $table = 'mantrajap_device';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'fkleadform_id',
        'device_id',
        'os',
        'version',
        'device_name',
    ];
}
