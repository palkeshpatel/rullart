<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $table = 'pages';
    protected $primaryKey = 'pageid';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'pagetitle',
        'pagetitleAR',
        'pagename',
        'photo',
        'details',
        'detailsAR',
        'metatitle',
        'metakeyword',
        'metadescription',
        'updateddate',
        'fkuserid',
        'published',
    ];
}

