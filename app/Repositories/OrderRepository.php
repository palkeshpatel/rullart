<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class OrderRepository
{
    /**
     * Get customer orders
     */
    public function getCustomerOrders($customerId, $perPage = 10)
    {
        return DB::table('ordermaster as om')
            ->select([
                'om.*',
                DB::raw('(SELECT SUM(qty) FROM orderitems WHERE fkorderid = om.orderid) as itemqty'),
                'os.status',
                'os.statusAR',
                'os.classname'
            ])
            ->join('orderstatus as os', 'om.fkorderstatus', '=', 'os.statusid')
            ->where('om.fkcustomerid', $customerId)
            ->orderBy('om.orderdate', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get order by order number
     */
    public function getOrderByOrderNo($orderNo, $customerId = null)
    {
        $query = DB::table('ordermaster')
            ->where('orderno', $orderNo);

        if ($customerId) {
            $query->where('fkcustomerid', $customerId);
        }

        return $query->first();
    }

    /**
     * Get order items
     */
    public function getOrderItems($orderId)
    {
        return DB::table('orderitems')
            ->where('fkorderid', $orderId)
            ->get();
    }

    /**
     * Get total order count for customer
     */
    public function getTotalOrderCount($customerId)
    {
        return DB::table('ordermaster')
            ->where('fkcustomerid', $customerId)
            ->count();
    }
}
