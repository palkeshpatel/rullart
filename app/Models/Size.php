<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Size extends Model
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

    // Scope to filter by fkfilterid = 3 (Size from filtermaster)
    public function scopeSizes($query)
    {
        return $query->where('fkfilterid', 3);
    }
}
