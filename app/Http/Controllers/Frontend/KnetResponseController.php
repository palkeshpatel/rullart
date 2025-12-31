<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Frontend\FrontendController;
use App\Repositories\OrderRepository;
use App\Repositories\ShoppingCartRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class KnetResponseController extends FrontendController
{
    protected $orderRepository;
    protected $cartRepository;

    public function __construct(
        OrderRepository $orderRepository,
        ShoppingCartRepository $cartRepository
    ) {
        parent::__construct();
        $this->orderRepository = $orderRepository;
        $this->cartRepository = $cartRepository;
    }

    public function index(Request $request)
    {
        $locale = app()->getLocale();
        
        // Get islive setting (0 = test, 1 = live)
        $islive = config('app.islive', 0);
        
        if ($islive == 0) {
            $websiteurl = url('/');
            $termResourceKey = "21BUP5TA2W2G10F7";
        } else {
            $websiteurl = "https://www.rullart.com/";
            $termResourceKey = "BY1B4R657T394AFH";
        }

        // Get parameters from Knet (can be in GET or POST)
        $ResErrorText = $request->input('ErrorText', '');
        $ResTranData = $request->input('trandata', '');
        $paymentid = $request->input('paymentid', '');
        $trackid = $request->input('trackid', '');
        $ResErrorNo = $request->input('Error', '');
        $result = $request->input('result', '');
        $postdate = $request->input('postdate', '');
        $tranid = $request->input('tranid', '');
        $auth = $request->input('auth', '');
        $ref = $request->input('ref', '');
        $ResAmount = $request->input('amt', '');

        $trandate = date('Y-m-d H:i:s');

        // If error is present in URL params, redirect to error page
        if ($ResErrorText || $ResErrorNo) {
            return redirect()->route('order.error', [
                'locale' => $locale,
                'ErrorText' => $ResErrorText,
                'Error' => $ResErrorNo,
                'trackid' => $trackid,
                'amt' => $ResAmount,
                'paymentid' => $paymentid,
            ]);
        }

        // If no error, decrypt trandata
        if ($ResTranData) {
            try {
                // Decrypt the response
                $decryptedData = $this->decrypt($ResTranData, $termResourceKey);
                $decryptedData = $this->queryToArray($decryptedData);

                if (!$decryptedData) {
                    Log::error('KnetResponse: Failed to decrypt or parse trandata', [
                        'trandata' => substr($ResTranData, 0, 100) . '...'
                    ]);
                    return redirect()->route('order.error', [
                        'locale' => $locale,
                        'ErrorText' => 'Failed to decrypt payment response',
                    ]);
                }

                // Extract data from decrypted response
                $result = $decryptedData['result'] ?? '';
                $trackid = $decryptedData['trackid'] ?? '';
                $paymentid = $decryptedData['paymentid'] ?? '';
                $ref = $decryptedData['ref'] ?? '';
                $tranid = $decryptedData['tranid'] ?? '';
                $amount = $decryptedData['amt'] ?? '';
                $trx_error = $decryptedData['Error'] ?? '';
                $trx_errortext = $decryptedData['ErrorText'] ?? '';
                $postdate = $decryptedData['postdate'] ?? '';
                $auth = $decryptedData['auth'] ?? '';
                $udf1 = $decryptedData['udf1'] ?? '';
                $udf2 = $decryptedData['udf2'] ?? '';
                $cartid = $decryptedData['udf3'] ?? ''; // Original cart ID stored in UDF3
                $udf4 = $decryptedData['udf4'] ?? '';
                $udf5 = $decryptedData['udf5'] ?? '';

                // Extract cart ID from track ID if needed (format: timestamp_cartid)
                if (!$cartid && $trackid && strpos($trackid, '_') !== false) {
                    $parts = explode('_', $trackid);
                    if (count($parts) >= 2) {
                        $cartid = end($parts); // Get the last part (cart ID)
                    }
                }

                // Check if order already processed
                $orderExistId = $this->checkOrderProcessed($paymentid);
                if ($orderExistId > 0) {
                    return redirect('/' . $locale . '/thankyou?orderid=' . $orderExistId);
                }

                // Save payment data
                $paymentData = [
                    'paymentid' => $paymentid,
                    'result' => $result,
                    'postdate' => $postdate,
                    'tranid' => $tranid,
                    'auth' => $auth,
                    'ref' => $ref,
                    'trackid' => $trackid,
                    'udf1' => $udf1,
                    'udf2' => $udf2,
                    'udf3' => $cartid,
                    'udf4' => $udf4,
                    'udf5' => $udf5,
                    'submiton' => $trandate,
                    'fkcustomerid' => $udf2 ?: 0,
                ];

                $payid = DB::table('payments')->insertGetId($paymentData);

                // Get cart data
                $cartData = $this->cartRepository->getCartData($cartid, $locale);
                $cartMaster = $cartData['shoppingcart'] ?? null;

                if (!$cartMaster) {
                    Log::error('KnetResponse: Cart not found', ['cartid' => $cartid]);
                    return redirect()->route('order.error', [
                        'locale' => $locale,
                        'ErrorText' => 'Cart not found',
                        'trackid' => $trackid,
                    ]);
                }

                // Process payment result
                if ($result == "CAPTURED") {
                    // Create order
                    $orderId = $this->createOrderFromCart($cartMaster, $paymentData, $payid, $paymentid, $tranid, $trandate, $locale);

                    if ($orderId) {
                        // Clear coupon session
                        Session::forget('couponcode');
                        Session::forget('couponvalue');

                        // Redirect to thank you page
                        return redirect('/' . $locale . '/thankyou?orderid=' . $orderId . '&paymentid=' . $paymentid . '&result=' . $result . '&tranID=' . $tranid . '&auth=' . $auth . '&ref=' . $ref . '&trackid=' . $trackid);
                    } else {
                        Log::error('KnetResponse: Failed to create order', ['cartid' => $cartid]);
                        return redirect()->route('order.error', [
                            'locale' => $locale,
                            'ErrorText' => 'Failed to create order',
                            'trackid' => $trackid,
                        ]);
                    }
                } else {
                    // Payment failed
                    return redirect()->route('order.error', [
                        'locale' => $locale,
                        'ErrorText' => $trx_errortext ?: 'Payment was not successful',
                        'trackid' => $trackid,
                        'paymentid' => $paymentid,
                        'result' => $result,
                        'tranID' => $tranid,
                        'auth' => $auth,
                        'ref' => $ref,
                        'postdate' => $postdate,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('KnetResponse: Exception processing payment', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return redirect()->route('order.error', [
                    'locale' => $locale,
                    'ErrorText' => 'Error processing payment: ' . $e->getMessage(),
                ]);
            }
        } else {
            // No trandata, redirect to error
            return redirect()->route('order.error', [
                'locale' => $locale,
                'ErrorText' => 'No payment data received',
            ]);
        }
    }

    /**
     * Check if order is already processed
     */
    protected function checkOrderProcessed($paymentid)
    {
        $order = DB::table('payments as ph')
            ->join('ordermaster as om', 'om.fkcartid', '=', DB::raw('CAST(ph.udf3 AS UNSIGNED)'))
            ->where('ph.paymentid', $paymentid)
            ->first();

        return $order ? $order->orderid : 0;
    }

    /**
     * Create order from cart
     */
    protected function createOrderFromCart($cartMaster, $paymentData, $payid, $paymentid, $tranid, $trandate, $locale)
    {
        try {
            // Get cart items
            $cartItems = DB::table('shoppingcartitems')
                ->where('fkcartid', $cartMaster->cartid)
                ->get();

            if ($cartItems->isEmpty()) {
                return null;
            }

            // Get address data from cart
            $shippingAddress = null;
            $billingAddress = null;

            if ($cartMaster->shippingaddressid > 0) {
                $shippingAddress = DB::table('addressbook')
                    ->where('addressid', $cartMaster->shippingaddressid)
                    ->first();
            }

            if ($cartMaster->billingaddressid > 0) {
                $billingAddress = DB::table('addressbook')
                    ->where('addressid', $cartMaster->billingaddressid)
                    ->first();
            } else {
                $billingAddress = $shippingAddress;
            }

            // Prepare order data
            $orderData = [
                'firstname' => $shippingAddress->firstname ?? '',
                'lastname' => $shippingAddress->lastname ?? '',
                'title' => $shippingAddress->title ?? '',
                'fkcustomerid' => $cartMaster->fkcustomerid,
                'mobile' => $shippingAddress->mobile ?? '',
                'country' => $shippingAddress->country ?? 'Kuwait',
                'areaname' => $shippingAddress->areaname ?? '',
                'address' => $shippingAddress->address ?? '',
                'addressid' => $cartMaster->shippingaddressid,
                'firstnameBill' => $billingAddress->firstname ?? '',
                'lastnameBill' => $billingAddress->lastname ?? '',
                'mobileBill' => $billingAddress->mobile ?? '',
                'countryBill' => $billingAddress->country ?? 'Kuwait',
                'areanameBill' => $billingAddress->areaname ?? '',
                'addressBill' => $billingAddress->address ?? '',
                'itemtotal' => $cartMaster->itemtotal ?? 0,
                'shipping_charge' => $cartMaster->shipping_charge ?? 0,
                'total' => $cartMaster->total ?? 0,
                'asGift' => $cartMaster->asGift ?? '0',
                'giftMessage' => $cartMaster->giftMessage ?? '',
                'fkorderstatus' => 2, // Order confirmed
                'paymentmethod' => 'Knet',
                'orderdate' => $trandate,
                'payid' => $payid,
                'paymentid' => $paymentid,
                'tranid' => $tranid,
                'lang' => $locale,
                'currencycode' => Session::get('currencycode', 'KWD'),
                'currencyrate' => Session::get('currencyrate', 1),
                'fkcartid' => $cartMaster->cartid,
                'couponcode' => $cartMaster->couponcode ?? '',
                'couponvalue' => $cartMaster->couponvalue ?? 0,
                'discount' => $cartMaster->discount ?? 0,
                'vat_percent' => $cartMaster->vat_percent ?? 0,
                'vat' => $cartMaster->vat ?? 0,
                'shipping_method' => $cartMaster->shipping_method ?? 'standard',
                'delivery_method' => $cartMaster->delivery_method ?? 'Regular Delivery',
            ];

            // Insert order
            $orderId = DB::table('ordermaster')->insertGetId($orderData);

            // Insert order items
            foreach ($cartItems as $item) {
                DB::table('orderitems')->insert([
                    'fkorderid' => $orderId,
                    'fkproductid' => $item->fkproductid,
                    'productname' => $item->productname,
                    'productcode' => $item->productcode,
                    'qty' => $item->qty,
                    'price' => $item->price,
                    'total' => $item->total,
                    'size' => $item->size ?? '',
                    'color' => $item->color ?? '',
                ]);
            }

            return $orderId;
        } catch (\Exception $e) {
            Log::error('KnetResponse: Error creating order', [
                'error' => $e->getMessage(),
                'cartid' => $cartMaster->cartid ?? null
            ]);
            return null;
        }
    }

    /**
     * Decrypt Knet response data
     */
    protected function decrypt($code, $key)
    {
        $code = $this->hex2ByteArray(trim($code));
        $code = $this->byteArray2String($code);
        $iv = $key;
        $code = base64_encode($code);
        $decrypted = openssl_decrypt($code, 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $iv);
        return $this->pkcs5_unpad($decrypted);
    }

    protected function hex2ByteArray($hexString)
    {
        $string = hex2bin($hexString);
        return unpack('C*', $string);
    }

    protected function byteArray2String($byteArray)
    {
        $chars = array_map("chr", $byteArray);
        return join($chars);
    }

    protected function pkcs5_unpad($text)
    {
        if (empty($text)) {
            return false;
        }
        $pad = ord($text[strlen($text) - 1]);
        if ($pad > strlen($text)) {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }

    protected function queryToArray($qry)
    {
        $result = [];
        if (substr($qry, -1) == "&") {
            $qry = substr($qry, 0, -1);
        }
        
        if (strpos($qry, '=')) {
            if (strpos($qry, '?') !== false) {
                $q = parse_url($qry);
                $qry = $q['query'] ?? $qry;
            }
        } else {
            return false;
        }

        foreach (explode('&', $qry) as $couple) {
            if (strpos($couple, '=') !== false) {
                list($key, $val) = explode('=', $couple, 2);
                $result[$key] = urldecode($val);
            }
        }

        return empty($result) ? false : $result;
    }
}
