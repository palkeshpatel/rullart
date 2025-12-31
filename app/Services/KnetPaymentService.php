<?php

namespace App\Services;

class KnetPaymentService
{
    private $orderid;
    private $amount;
    private $TranportalId;
    private $TranportalPassword;
    private $ResponseUrl;
    private $ErrorUrl;
    private $udf1;
    private $udf2;
    private $udf3;
    private $udf4;
    private $Language;
    private $termResourceKey;
    private $islive;

    public function __construct()
    {
        // Test credentials (islive = 0)
        $this->TranportalId = "196901";
        $this->TranportalPassword = "196901pg";
        $this->termResourceKey = "21BUP5TA2W2G10F7";
        
        // Live credentials (islive = 1) - will be set if needed
        // $this->TranportalId = "229801";
        // $this->TranportalPassword = "7WhRfBdE";
        // $this->termResourceKey = "BY1B4R657T394AFH";
        
        $this->islive = config('app.islive', 0);
        
        // Override with live credentials if islive = 1
        if ($this->islive == 1) {
            $this->TranportalId = "229801";
            $this->TranportalPassword = "7WhRfBdE";
            $this->termResourceKey = "BY1B4R657T394AFH";
        }
    }

    public function setOrderId($orderid)
    {
        $this->orderid = $orderid;
        return $this;
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    public function setResponseUrl($url)
    {
        $this->ResponseUrl = $url;
        return $this;
    }

    public function setErrorUrl($url)
    {
        $this->ErrorUrl = $url;
        return $this;
    }

    public function setUdf1($value)
    {
        $this->udf1 = $value;
        return $this;
    }

    public function setUdf2($value)
    {
        $this->udf2 = $value;
        return $this;
    }

    public function setUdf3($value)
    {
        $this->udf3 = $value;
        return $this;
    }

    public function setUdf4($value)
    {
        $this->udf4 = $value;
        return $this;
    }

    public function setLanguage($lang)
    {
        $this->Language = $lang == 'ar' ? 'ARA' : 'ENG';
        return $this;
    }

    public function performPayment()
    {
        $TranAmount = $this->amount;
        $TranTrackid = $this->orderid;
        $TranportalId = $this->TranportalId;

        $ReqTranportalId = "id=" . $TranportalId;
        $ReqTranportalPassword = "password=" . $this->TranportalPassword;
        $ReqAmount = "amt=" . $TranAmount;
        $ReqTrackId = "trackid=" . $TranTrackid;
        $ReqCurrency = "currencycode=414"; // KWD

        if ($this->Language == "ENG") {
            $ReqLangid = "langid=USA";
        } else {
            $ReqLangid = "langid=AR";
        }

        $ReqAction = "action=1";
        $ReqResponseUrl = "responseURL=" . $this->ResponseUrl;
        $ReqErrorUrl = "errorURL=" . $this->ErrorUrl;
        $ReqUdf1 = "udf1=" . $this->udf1;
        $ReqUdf2 = "udf2=" . $this->udf2;
        $ReqUdf3 = "udf3=" . $this->udf3;
        $ReqUdf4 = "udf4=" . $this->udf4;

        $param = $ReqTranportalId . "&" . $ReqTranportalPassword . "&" . $ReqAction . "&" . $ReqLangid . "&" . $ReqCurrency . "&" . $ReqAmount . "&" . $ReqResponseUrl . "&" . $ReqErrorUrl . "&" . $ReqTrackId . "&" . $ReqUdf1 . "&" . $ReqUdf2 . "&" . $ReqUdf3 . "&" . $ReqUdf4;

        // Encrypt
        $encrypted = $this->encryptAES($param, $this->termResourceKey) . "&tranportalId=" . $TranportalId . "&responseURL=" . $this->ResponseUrl . "&errorURL=" . $this->ErrorUrl;

        if ($this->islive == 0) {
            $retval = "https://kpaytest.com.kw/kpg/PaymentHTTP.htm?param=paymentInit" . "&trandata=" . $encrypted;
        } else {
            $retval = "https://www.kpay.com.kw/kpg/PaymentHTTP.htm?param=paymentInit" . "&trandata=" . $encrypted;
        }

        return $retval;
    }

    private function encryptAES($str, $key)
    {
        $str = $this->pkcs5_pad($str);
        $encrypted = openssl_encrypt($str, 'AES-128-CBC', $key, OPENSSL_ZERO_PADDING, $key);
        $encrypted = base64_decode($encrypted);
        $encrypted = unpack('C*', ($encrypted));
        $encrypted = $this->byteArray2Hex($encrypted);
        $encrypted = urlencode($encrypted);
        return $encrypted;
    }

    private function pkcs5_pad($text)
    {
        $blocksize = 16;
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    private function byteArray2Hex($byteArray)
    {
        $chars = array_map("chr", $byteArray);
        $bin = join($chars);
        return bin2hex($bin);
    }
}

