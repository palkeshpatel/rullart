<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('customer');

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('fkorderstatus', $request->status);
        }

        // Filter by country
        if ($request->has('country') && $request->country && $request->country !== '--All Country--') {
            $query->where('country', $request->country);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('orderid', 'like', "%{$search}%")
                  ->orWhere('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($customerQuery) use ($search) {
                      $customerQuery->where('email', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = $request->get('per_page', 25);
        $orders = $query->orderBy('orderdate', 'desc')->paginate($perPage);

        // Get unique countries for filter
        $countries = Order::select('country')->distinct()->whereNotNull('country')->orderBy('country')->pluck('country');

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.orders.partials.table', compact('orders'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $orders])->render(),
                'hasFilters' => $request->has('status') || $request->has('country') || $request->has('search'),
            ]);
        }

        return view('admin.orders.index', compact('orders', 'countries'));
    }

    public function show(Request $request, $id)
    {
        $order = Order::with(['customer', 'items.product'])
            ->where('orderid', $id)
            ->firstOrFail();

        // Ensure items are loaded - try both relationship and direct query
        $items = OrderItem::where('fkorderid', $order->orderid)
            ->with('product')
            ->get();
        
        // Set items collection on order object
        $order->setRelation('items', $items);

        // Get order statuses for dropdown
        $orderStatuses = DB::table('orderstatus')->get();

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.orders.partials.modal', compact('order', 'orderStatuses'))->render(),
            ]);
        }

        return view('admin.orders.show', compact('order', 'orderStatuses'));
    }

    public function edit($id)
    {
        $order = Order::with(['customer', 'items.product'])
            ->where('orderid', $id)
            ->firstOrFail();

        // Ensure items are loaded - try both relationship and direct query
        $items = OrderItem::where('fkorderid', $order->orderid)
            ->with('product')
            ->get();
        
        // Set items collection on order object
        $order->setRelation('items', $items);

        // Get order statuses for dropdown
        $orderStatuses = DB::table('orderstatus')->get();

        return view('admin.orders.edit', compact('order', 'orderStatuses'));
    }

    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        // Update order status
        if ($request->has('fkorderstatus')) {
            $order->fkorderstatus = $request->fkorderstatus;
        }

        // Update tracking number
        if ($request->has('trackingno')) {
            $order->trackingno = $request->trackingno;
        }

        $order->save();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Order updated successfully'
            ]);
        }

        return redirect()->route('admin.orders.edit', $id)
            ->with('success', 'Order updated successfully');
    }
}
