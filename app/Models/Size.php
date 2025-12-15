<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Size extends Model
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

    // Scope to filter by commonname = 'size'
    public function scopeSizes($query)
    {
        return $query->where('commonname', 'size');
    }
}
