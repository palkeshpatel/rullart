<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Occassion extends Model
{
    protected $table = 'occassion';
    protected $primaryKey = 'occassionid';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'occassion',
        'occassionAR',
        'occassioncode',
        'photo',
        'ispublished',
        'metakeyword',
        'metadescr',
        'metatitle',
        'updateddate',
        'updatedby',
        'showhome',
        'photo_mobile',
        'metatitleAR',
        'metadescrAR',
        'metakeywordAR',
    ];
}
