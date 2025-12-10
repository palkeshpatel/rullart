<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnRequest extends Model
{
    protected $table = 'returnrequest';
    protected $primaryKey = 'requestid';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'firstname', 'lastname', 'orderno', 'email',
        'mobile', 'reason', 'submiton', 'lang'
    ];
}
