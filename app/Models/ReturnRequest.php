<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturnRequest extends Model
{
    protected $table = 'returnrequest';
    protected $primaryKey = 'requestid';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'firstname', 'lastname', 'orderno', 'email',
        'mobile', 'reason', 'submiton', 'lang'
    ];

    // Return request doesn't have direct foreign keys, but we can find order by orderno
    public function order()
    {
        return $this->hasOne(Order::class, 'orderid', 'orderno');
    }
}
