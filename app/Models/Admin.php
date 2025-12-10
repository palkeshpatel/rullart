<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $table = 'admin';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'user',
        'pass',
        'name',
        'email',
        'site',
        'user_role',
        'd_add',
        'd_mod',
        'lock_access',
        'fkstoreid',
        'created_date',
    ];

    protected $hidden = [
        'pass',
    ];

    /**
     * Get the password attribute (alias for 'pass')
     */
    public function getAuthPassword()
    {
        return $this->pass;
    }

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName()
    {
        return 'user';
    }

    /**
     * Get the unique identifier for the user.
     * This should return the integer ID for session storage.
     */
    public function getAuthIdentifier()
    {
        return $this->id;
    }
}

