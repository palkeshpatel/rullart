<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Color extends Model
{
    protected $table = 'filtervalues';
    protected $primaryKey = 'filtervalueid';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'fkfilterid',
        'filtervalue',
        'filtervalueAR',
        'filtervaluecode',
        'isactive',
        'displayorder',
    ];

    // Scope to filter by fkfilterid = 2 (Color from filtermaster)
    public function scopeColors($query)
    {
        return $query->where('fkfilterid', 2);
    }
}
