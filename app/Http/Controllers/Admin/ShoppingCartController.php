<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShoppingCartMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShoppingCartController extends Controller
{
    public function index(Request $request)
    {
        // Based on CI project - Get incomplete shopping carts using NOT EXISTS
        // Carts that haven't been converted to orders
        $query = ShoppingCartMaster::with(['customer', 'addressbook'])
            ->whereDoesntHave('order');

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('cartid', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('firstname', 'like', "%{$search}%")
                            ->orWhere('lastname', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by country
        if ($request->filled('country') && $request->country !== '' && $request->country !== '--All Country--') {
            $query->whereHas('addressbook', function ($addressQuery) use ($request) {
                $addressQuery->where('country', $request->country);
            });
        }

        // Pagination with limit
        $perPage = $request->get('per_page', 25);
        $carts = $query->orderBy('orderdate', 'desc')->paginate($perPage);

        // Get unique countries for filter dropdown
        // Only get countries that have incomplete shopping carts (same logic as main query)
        $countries = ShoppingCartMaster::whereDoesntHave('order')
            ->whereHas('addressbook', function ($query) {
                $query->whereNotNull('country');
            })
            ->with('addressbook')
            ->get()
            ->pluck('addressbook.country')
            ->filter()
            ->unique()
            ->sort()
            ->values();

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

    public function show(Request $request, $id)
    {
        // Get cart with customer and address information
        $cart = ShoppingCartMaster::with(['customer', 'addressbook', 'items.product'])
            ->where('cartid', $id)
            ->firstOrFail();

        // Get cart items with product information
        $cartItems = $cart->items;

        // Return JSON for AJAX modal requests
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.orders-not-process.partials.modal', compact('cart', 'cartItems'))->render(),
            ]);
        }

        return view('admin.orders-not-process.show', compact('cart', 'cartItems'));
    }

    public function destroy(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            // Get cart with customer ID before deleting
            $cart = ShoppingCartMaster::findOrFail($id);
            $customerid = $cart->fkcustomerid ?? 0;

            // Delete cart items first (cascade delete)
            $cart->items()->delete();

            // Delete cart master
            $cart->delete();

            // Create new empty cart for customer (following CI project pattern)
            if ($customerid > 0) {
                ShoppingCartMaster::create([
                    'fkcustomerid' => $customerid,
                    'fkstoreid' => 1,
                    'sessionid' => '',
                    'orderdate' => now(),
                ]);
            }

            DB::commit();

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Cart deleted successfully'
                ]);
            }

            return redirect()->route('admin.orders-not-process')
                ->with('success', 'Cart deleted successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting cart: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->route('admin.orders-not-process')
                ->with('error', 'Error deleting cart: ' . $e->getMessage());
        }
    }
}