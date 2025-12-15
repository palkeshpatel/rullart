<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GiftMessage extends Model
{
    protected $table = 'messages';
    protected $primaryKey = 'messageid';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'message',
        'messageAR',
        'isactive',
        'displayorder',
        'displayorderAR',
    ];
}
