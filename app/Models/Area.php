<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Area extends Model
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

    // Scope to filter by commonname = 'area'
    public function scopeAreas($query)
    {
        return $query->where('commonname', 'area');
    }
}
