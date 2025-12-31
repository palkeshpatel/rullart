<?php

namespace App\Http\Controllers\Frontend;

use App\Repositories\OrderRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class MyOrdersController extends FrontendController
{
    protected $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

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
        $ordersPaginated = $this->orderRepository->getCustomerOrders($customerId, $pageSize);
        $orders = $ordersPaginated->items();

        // Format order numbers
        foreach ($orders as $key => $order) {
            $orders[$key]->orderno = $this->generateOrderNo($order->orderid, $order->orderdate);
        }

        $totalOrders = $ordersPaginated->total();
        $noOfPages = $ordersPaginated->lastPage();

        if ($currentPage > $noOfPages && $noOfPages > 0) {
            $currentPage = $noOfPages;
        }

        $data = [
            'locale' => $locale,
            'orders' => ['list' => $orders, 'count' => $totalOrders],
            'currentpage' => $currentPage,
            'pagesize' => $pageSize,
            'noofpage' => $noOfPages,
            'totalorders' => $totalOrders,
        ];

        return view('frontend.myorders.index', $data);
    }

    protected function generateOrderNo($orderId, $orderDate)
    {
        $yr = date('Y', strtotime($orderDate));
        $num = sprintf("%04d", $orderId);
        return $yr . $num;
    }
}

