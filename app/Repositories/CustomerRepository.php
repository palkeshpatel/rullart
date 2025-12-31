<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerRepository
{
    /**
     * Get customer by email
     */
    public function getCustomerByEmail($email)
    {
        return DB::table('customers')
            ->where('email', $email)
            ->first();
    }

    /**
     * Get customer by ID
     */
    public function getCustomerById($customerId)
    {
        return DB::table('customers')
            ->where('customerid', $customerId)
            ->first();
    }

    /**
     * Create new customer
     */
    public function createCustomer(array $data)
    {
        return DB::table('customers')->insertGetId($data);
    }

    /**
     * Update customer
     */
    public function updateCustomer($customerId, array $data)
    {
        return DB::table('customers')
            ->where('customerid', $customerId)
            ->update($data);
    }

    /**
     * Update customer password
     */
    public function updatePassword($customerId, $password)
    {
        // Support both MD5 (legacy) and bcrypt
        $hashedPassword = Hash::make($password);

        return DB::table('customers')
            ->where('customerid', $customerId)
            ->update([
                'password' => $hashedPassword,
                'password_md5' => md5($password) // Keep MD5 for backward compatibility
            ]);
    }

    /**
     * Get customer with cart count
     */
    public function getCustomerWithCartCount($customerId)
    {
        $customer = $this->getCustomerById($customerId);

        if ($customer) {
            $customer->cart_count = DB::table('shoppingcartitems')
                ->join('shoppingcartmaster', 'shoppingcartitems.fkcartid', '=', 'shoppingcartmaster.cartid')
                ->where('shoppingcartmaster.fkcustomerid', $customerId)
                ->sum('shoppingcartitems.qty') ?? 0;
        }

        return $customer;
    }
}
