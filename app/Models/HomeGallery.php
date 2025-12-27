<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HomeGallery extends Model
{
    protected $table = 'homegallery';
    protected $primaryKey = 'homegalleryid';
    public $incrementing = false; // Set to false since we're manually setting the ID
    public $timestamps = false;

    protected $fillable = [
        'title',
        'descr',
        'titleAR',
        'descrAR',
        'link',
        'photo',
        'photo_mobile',
        'photo_ar',
        'photo_mobile_ar',
        'updateddate',
        'updatedby',
        'displayorder',
        'ispublished',
        'videourl',
    ];
}

