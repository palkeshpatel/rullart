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
     * This should return 'id' since we store the ID in the session.
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * Get the unique identifier for the user.
     * This should return the integer ID for session storage.
     */
    public function getAuthIdentifier()
    {
        return $this->id;
    }

    /**
     * Disable remember token functionality
     * The admin table doesn't have remember_token column
     */
    public function getRememberToken()
    {
        return null;
    }

    /**
     * Disable remember token functionality
     */
    public function setRememberToken($value)
    {
        // Do nothing - remember_token column doesn't exist
    }

    /**
     * Disable remember token functionality
     */
    public function getRememberTokenName()
    {
        return null;
    }
}

