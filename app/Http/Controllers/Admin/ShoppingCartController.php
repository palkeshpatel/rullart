<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShoppingCartMaster;
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

        // Return view for initial page load
        return view('admin.orders-not-process.index', compact('countries'));
    }

    /**
     * Get DataTables data for server-side processing
     */
    private function getDataTablesData(Request $request)
    {
        try {
            $countQuery = ShoppingCartMaster::whereDoesntHave('order');
            $totalRecords = $countQuery->count();

            $query = ShoppingCartMaster::with(['customer', 'addressbook'])->whereDoesntHave('order');
            $filteredCountQuery = ShoppingCartMaster::whereDoesntHave('order');

            // Filter by country
            $country = $request->input('country');
            if (!empty($country) && $country !== '--All Country--') {
                $query->whereHas('addressbook', function ($addressQuery) use ($country) {
                    $addressQuery->where('country', $country);
                });
                $filteredCountQuery->whereHas('addressbook', function ($addressQuery) use ($country) {
                    $addressQuery->where('country', $country);
                });
            }

            $filteredCount = $filteredCountQuery->count();

            // DataTables search
            $searchValue = $request->input('search.value', '');
            if (!empty($searchValue)) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('cartid', 'like', "%{$searchValue}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($searchValue) {
                            $customerQuery->where('firstname', 'like', "%{$searchValue}%")
                                ->orWhere('lastname', 'like', "%{$searchValue}%")
                                ->orWhere('email', 'like', "%{$searchValue}%");
                        });
                });

                $filteredCountQuery->where(function ($q) use ($searchValue) {
                    $q->where('cartid', 'like', "%{$searchValue}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($searchValue) {
                            $customerQuery->where('firstname', 'like', "%{$searchValue}%")
                                ->orWhere('lastname', 'like', "%{$searchValue}%")
                                ->orWhere('email', 'like', "%{$searchValue}%");
                        });
                });
            }

            $filteredAfterSearch = $filteredCountQuery->count();

            // Ordering
            $orderColumnIndex = $request->input('order.0.column', 0);
            $orderDir = $request->input('order.0.dir', 'desc');
            $columns = ['cartid', 'firstname', 'email', 'total', 'orderdate', 'paymentmethod', 'mobiledevice', 'emailcount', 'emailsenddate', 'cartid'];
            $orderColumn = $columns[$orderColumnIndex] ?? 'orderdate';
            $query->orderBy($orderColumn, $orderDir);

            // Pagination
            $start = $request->input('start', 0);
            $length = $request->input('length', 25);
            $carts = $query->skip($start)->take($length)->get();

            // Format data
            $data = [];
            foreach ($carts as $cart) {
                $name = trim(($cart->customer->firstname ?? '') . ' ' . ($cart->customer->lastname ?? ''));
                $orderFrom = '';
                if (isset($cart->mobiledevice) && $cart->mobiledevice) {
                    $orderFrom = ucfirst($cart->mobiledevice);
                    if (isset($cart->platform) && $cart->platform) {
                        $orderFrom .= ' ' . $cart->platform;
                    }
                } elseif (isset($cart->platform) && $cart->platform) {
                    $orderFrom = 'Web ' . $cart->platform;
                } elseif (isset($cart->browser) && $cart->browser) {
                    $orderFrom = 'Web ' . $cart->browser;
                } else {
                    $orderFrom = 'Web';
                }

                $data[] = [
                    'ref' => $cart->cartid ?? '',
                    'name' => $name ?: 'N/A',
                    'email' => $cart->customer->email ?? 'N/A',
                    'total' => number_format($cart->total ?? 0, 3),
                    'orderdate' => $cart->orderdate ? \Carbon\Carbon::parse($cart->orderdate)->format('d/M/Y H:i') : 'N/A',
                    'paymentmethod' => isset($cart->paymentmethod) && $cart->paymentmethod ? ucfirst($cart->paymentmethod) : 'N/A',
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
            
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

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
                $customerEmail = $cart->customer->email ?? 'N/A';
                $country = $cart->addressbook->country ?? 'N/A';
                
                fputcsv($file, [
                    $cart->cartid ?? 'N/A',
                    $customerName ?: 'N/A',
                    $customerEmail,
                    $country,
                    $cart->orderdate ? \Carbon\Carbon::parse($cart->orderdate)->format('d-M-Y H:i:s') : 'N/A',
                    number_format($cart->totalamount ?? 0, 3)
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers_array);
    }
}