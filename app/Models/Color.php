<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    protected $table = 'commonmaster';
    protected $primaryKey = 'commonid';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'commonname',
        'commonvalue',
        'commonvalueAR',
        'displayorder',
    ];

    // Scope to filter by commonname = 'color'
    public function scopeColors($query)
    {
        return $query->where('commonname', 'color');
    }
}
