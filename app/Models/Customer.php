<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'customerid';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'firstname', 'lastname', 'email', 'password', 'last_login',
        'login_ipaddress', 'login_type', 'isactive', 'language',
        'createdon', 'updatedby', 'updateddate', 'oauth_provider',
        'oauth_uid', 'gender', 'locale', 'picture_url', 'profile_url',
        'token', 'wishlist_email', 'wishlist_update', 'isnewsletter', 'fkstoreid'
    ];

    public function orders()
    {
        return $this->hasMany(Order::class, 'fkcustomerid', 'customerid');
    }
}
