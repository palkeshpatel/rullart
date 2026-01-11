<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShoppingCartMaster;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Log;
use Exception;

class ShoppingCartController extends Controller
{
    public function index(Request $request)
    {
        // Check if this is a DataTables request
        if ($request->has('draw')) {
            return $this->getDataTablesData($request);
        }

        // Get unique countries for filter dropdown
        // Get countries from countrymaster table ordered by countryid (like CI project does)
        $countries = Country::where('isactive', 1)
            ->orderBy('countryid')
            ->pluck('countryname')
            ->toArray();

        // Return view for initial page load
        return view('admin.orders-not-process.index', compact('countries'));
    }

    /**
     * Get DataTables data for server-side processing
     * Based on CI project: /application-LIVE/models/Ordersnotprocess_model.php
     */
    private function getDataTablesData(Request $request)
    {
        try {
            // Count all - using NOT IN subquery like CI project
            $totalRecords = DB::table('cartmaster')
                ->whereNotIn('cartid', function ($query) {
                    $query->select('fkcartid')->from('ordermaster');
                })
                ->count();

            // Base query - match CI project logic exactly
            // CI project uses: cartmaster o JOIN customers c ON o.fkcustomerid=c.customerid
            // Name from cartmaster.firstname, cartmaster.lastname (NOT from customers!)
            // Email from customers.email
            $query = DB::table('cartmaster as o')
                ->select(
                    'o.cartid',
                    DB::raw('CONCAT(o.firstname, " ", o.lastname) as name'),
                    'c.email',
                    'o.orderdate',
                    'o.total',
                    'o.paymentmethod',
                    'o.ismobile',
                    'o.platform',
                    DB::raw('CONCAT(CASE WHEN IFNULL(o.ismobile,0)=0 THEN "Web" ELSE "App" END, " ", IFNULL(o.platform,"")) as orderfrom')
                )
                ->join('customers as c', 'o.fkcustomerid', '=', 'c.customerid')
                ->whereNotIn('o.cartid', function ($subQuery) {
                    $subQuery->select('fkcartid')->from('ordermaster');
                });

            // Filter by country - CI project uses cartmaster.country field directly
            $country = $request->input('country');
            if (!empty($country) && $country !== '--All Country--') {
                $query->where('o.country', $country);
            }

            // DataTables search - include cartid for searching by cart ID
            $searchValue = $request->input('search.value', '');
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('o.cartid', 'like', "%{$searchValue}%")
                        ->orWhere('o.firstname', 'like', "%{$searchValue}%")
                        ->orWhere('o.lastname', 'like', "%{$searchValue}%")
                        ->orWhere('c.email', 'like', "%{$searchValue}%")
                        ->orWhere('o.paymentmethod', 'like', "%{$searchValue}%");
                });
            }

            // Count filtered - using same query structure
            $filteredCountQuery = DB::table('cartmaster as o')
                ->join('customers as c', 'o.fkcustomerid', '=', 'c.customerid')
                ->whereNotIn('o.cartid', function ($subQuery) {
                    $subQuery->select('fkcartid')->from('ordermaster');
                });

            if (!empty($country) && $country !== '--All Country--') {
                $filteredCountQuery->where('o.country', $country);
            }

            if (!empty($searchValue)) {
                $filteredCountQuery->where(function ($q) use ($searchValue) {
                    $q->where('o.cartid', 'like', "%{$searchValue}%")
                        ->orWhere('o.firstname', 'like', "%{$searchValue}%")
                        ->orWhere('o.lastname', 'like', "%{$searchValue}%")
                        ->orWhere('c.email', 'like', "%{$searchValue}%")
                        ->orWhere('o.paymentmethod', 'like', "%{$searchValue}%");
                });
            }

            $filteredAfterSearch = $filteredCountQuery->count();

            // Ordering - match CI project column_order
            $orderColumnIndex = $request->input('order.0.column', 0);
            $orderDir = $request->input('order.0.dir', 'desc');
            $columns = [
                'o.cartid',
                DB::raw('CONCAT(o.firstname, " ", o.lastname)'), // name
                'c.email', // email
                'o.total',
                'o.orderdate',
                'o.paymentmethod',
                DB::raw('CONCAT(CASE WHEN IFNULL(o.ismobile,0)=0 THEN "Web" ELSE "App" END, " ", IFNULL(o.platform,""))'), // orderfrom
            ];
            $orderColumn = $columns[$orderColumnIndex] ?? 'o.orderdate';
            $query->orderBy($orderColumn, $orderDir);

            // Pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 25);
            $carts = $query->skip($start)->take($length)->get();

            // Format data - match CI project output format
            $data = [];
            foreach ($carts as $cart) {
                $name = $cart->name ?? 'N/A';
                $email = $cart->email ?? 'N/A';
                $orderFrom = $cart->orderfrom ?? 'Web';

                // Format paymentmethod
                $paymentmethod = !empty($cart->paymentmethod) ? $cart->paymentmethod : 'N/A';

                $data[] = [
                    'ref' => $cart->cartid ?? '',
                    'name' => $name,
                    'email' => $email,
                    'total' => number_format($cart->total ?? 0, 3),
                    'orderdate' => $cart->orderdate ? \Carbon\Carbon::parse($cart->orderdate)->format('d/M/Y H:i') : 'N/A',
                    'paymentmethod' => $paymentmethod,
                    'orderfrom' => $orderFrom,
                    'emailcount' => '0',
                    'emailsenddate' => '-',
                    'action' => $cart->cartid
                ];
            }

            return response()->json([
                'draw' => intval($request->input('draw')),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $filteredAfterSearch,
                'data' => $data
            ]);
        } catch (Exception $e) {
            Log::error('Orders Not Process DataTables Error: ' . $e->getMessage());
            return response()->json([
                'draw' => intval($request->input('draw', 1)),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'An error occurred while loading data.'
            ], 500);
        }
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

    public function export(Request $request)
    {
        $query = ShoppingCartMaster::with(['customer', 'addressbook'])
            ->whereDoesntHave('order');

        // Apply same filters as index method
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

        if ($request->filled('country') && $request->country !== '' && $request->country !== '--All Country--') {
            $query->whereHas('addressbook', function ($addressQuery) use ($request) {
                $addressQuery->where('country', $request->country);
            });
        }

        $carts = $query->orderBy('orderdate', 'desc')->get();

        $filename = 'shopping_carts_' . date('Y-m-d_His') . '.csv';

        $headers_array = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($carts) {
            $file = fopen('php://output', 'w');

            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, ['Shopping Carts (Not Completed) Export']);
            fputcsv($file, ['Generated on: ' . date('d-M-Y H:i:s')]);
            fputcsv($file, []);

            fputcsv($file, [
                'Cart ID',
                'Customer Name',
                'Customer Email',
                'Country',
                'Cart Date',
                'Total Amount'
            ]);

            foreach ($carts as $cart) {
                $customerName = $cart->customer ? trim(($cart->customer->firstname ?? '') . ' ' . ($cart->customer->lastname ?? '')) : 'N/A';
                $customerEmail = ($cart->customer && $cart->customer->email) ? $cart->customer->email : 'N/A';
                $country = ($cart->addressbook && $cart->addressbook->country) ? $cart->addressbook->country : 'N/A';

                fputcsv($file, [
                    $cart->cartid ?? 'N/A',
                    $customerName ?: 'N/A',
                    $customerEmail,
                    $country,
                    $cart->orderdate ? \Carbon\Carbon::parse($cart->orderdate)->format('d-M-Y H:i:s') : 'N/A',
                    number_format($cart->total ?? 0, 3)
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers_array);
    }
}