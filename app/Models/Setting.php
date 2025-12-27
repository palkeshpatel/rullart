<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'settingid';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'name',
        'details',
        'inputtype',
        'isrequired',
        'displayorder',
    ];
}

