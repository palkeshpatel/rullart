<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShoppingCart;
use App\Models\Customer;
use App\Models\AddressBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShoppingCartController extends Controller
{
    public function index(Request $request)
    {
        // Simple approach: Show all shopping carts that haven't been converted to orders
        // A cart is "incomplete" if there's no order with fkcartid matching shoppingcartid

        $query = ShoppingCart::with(['customer', 'addressbook'])
            ->leftJoin('ordermaster', function ($join) {
                $join->on('shoppingcart.shoppingcartid', '=', 'ordermaster.fkcartid')
                    ->where('ordermaster.fkcartid', '>', 0);
            })
            ->leftJoin('shoppingcartmaster', 'shoppingcart.shoppingcartid', '=', 'shoppingcartmaster.cartid')
            ->whereNull('ordermaster.orderid') // No order exists for this cart = incomplete
            ->select('shoppingcart.*', 'shoppingcartmaster.paymentmethod', 'shoppingcartmaster.platform', 'shoppingcartmaster.mobiledevice', 'shoppingcartmaster.browser');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', function ($customerQuery) use ($search) {
                    $customerQuery->where('firstname', 'like', "%{$search}%")
                        ->orWhere('lastname', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('shoppingcart.shoppingcartid', 'like', "%{$search}%");
            });
        }

        // Filter by country if provided (from addressbook)
        if ($request->has('country') && $request->country && $request->country !== '--All Country--') {
            $query->whereHas('addressbook', function ($q) use ($request) {
                $q->where('country', $request->country);
            });
        }

        $perPage = $request->get('per_page', 25);
        $carts = $query->orderBy('shoppingcart.updatedon', 'desc')->paginate($perPage);

        // Get unique countries for filter from addressbook that are linked to incomplete carts
        $countries = AddressBook::select('addressbook.country')
            ->join('shoppingcart', 'addressbook.addressid', '=', 'shoppingcart.fkaddressbookid')
            ->leftJoin('ordermaster', function ($join) {
                $join->on('shoppingcart.shoppingcartid', '=', 'ordermaster.fkcartid')
                    ->where('ordermaster.fkcartid', '>', 0);
            })
            ->whereNull('ordermaster.orderid')
            ->whereNotNull('addressbook.country')
            ->distinct()
            ->orderBy('addressbook.country')
            ->pluck('addressbook.country');

        // Return JSON for AJAX requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.orders-not-process.partials.table', compact('carts'))->render(),
                'pagination' => view('admin.partials.pagination', ['items' => $carts])->render(),
            ]);
        }

        return view('admin.orders-not-process.index', compact('carts', 'countries'));
    }
}