<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class MyOrdersController extends FrontendController
{
    public function index($locale, Request $request)
    {
        if (!Session::get('logged_in')) {
            return redirect()->route('home', ['locale' => $locale]);
        }

        $currentPage = $request->get('page', 1);
        $pageSize = 5;

        if (!is_numeric($currentPage) || $currentPage < 1) {
            $currentPage = 1;
        }

        $customerId = Session::get('customerid');

        // Get orders with pagination
        $orders = $this->getOrdersList($customerId, $currentPage - 1, $pageSize);

        // Format order numbers
        if ($orders && isset($orders['list'])) {
            foreach ($orders['list'] as $key => $order) {
                $orders['list'][$key]->orderno = $this->generateOrderNo($order->orderid, $order->orderdate);
            }
        }

        $totalOrders = $orders ? $orders['count'] : 0;
        $noOfPages = ceil($totalOrders / $pageSize);

        if ($currentPage > $noOfPages && $noOfPages > 0) {
            $currentPage = $noOfPages;
        }

        $data = [
            'locale' => $locale,
            'orders' => $orders,
            'currentpage' => $currentPage,
            'pagesize' => $pageSize,
            'noofpage' => $noOfPages,
            'totalorders' => $totalOrders,
        ];

        return view('frontend.myorders.index', $data);
    }

    protected function getOrdersList($customerId, $pageNo, $pageSize)
    {
        $orders = DB::table('ordermaster as om')
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
            ->skip($pageNo * $pageSize)
            ->take($pageSize)
            ->get();

        if ($orders->count() > 0) {
            $totalCount = DB::table('ordermaster')
                ->where('fkcustomerid', $customerId)
                ->count();

            return [
                'list' => $orders,
                'count' => $totalCount
            ];
        }

        return false;
    }

    protected function generateOrderNo($orderId, $orderDate)
    {
        $yr = date('Y', strtotime($orderDate));
        $num = sprintf("%04d", $orderId);
        return $yr . $num;
    }
}

