<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'notificationid';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'notificationid',
        'fkcustomerid',
        'device_uid',
        'title',
        'message',
        'isread',
        'createdby',
        'createdon',
        'response',
        'badge',
        'redirect_type',
        'redirect_code',
        'photo'
    ];
}

