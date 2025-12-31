<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Frontend\FrontendController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class OrderErrorController extends FrontendController
{
    public function index(Request $request)
    {
        $locale = app()->getLocale();
        
        // Get error parameters from Knet
        $errorText = $request->get('ErrorText', '');
        $error = $request->get('Error', '');
        $trackid = $request->get('trackid', '');
        $paymentid = $request->get('paymentid', '');
        $result = $request->get('result', '');
        $amt = $request->get('amt', '');
        
        // Extract cart ID from track ID if it's in format timestamp_cartid
        $cartId = $trackid;
        if (strpos($trackid, '_') !== false) {
            $parts = explode('_', $trackid);
            if (count($parts) >= 2) {
                $cartId = end($parts); // Get the last part (cart ID)
            }
        }
        
        $data = [
            'locale' => $locale,
            'errorMessage' => $errorText ?: __('Payment Incomplete'),
            'error' => $error,
            'errorText' => $errorText,
            'trackid' => $trackid,
            'cartId' => $cartId,
            'paymentid' => $paymentid,
            'result' => $result,
            'amt' => $amt,
        ];
        
        return view('frontend.ordererror.index', $data);
    }
}

