<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CourierCompany extends Model
{
    protected $table = 'courier_company';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'name',
        'tracking_url',
        'isactive',
        'created_at',
    ];
}

