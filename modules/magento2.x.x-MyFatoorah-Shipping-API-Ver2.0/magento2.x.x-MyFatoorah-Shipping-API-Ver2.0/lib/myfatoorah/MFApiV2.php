<?php

class MFApiV2 {

//-----------------------------------------------------------------------------------------------------------------------------------------

    public $apiURL;
    public $apiKey;
    public $loggerObj;
    public $loggerFunc;
    public $isDirectPayment = false;

    /**
     * Constructor
     */
    public function __construct($apiKey, $isTest, $loggerObj = null, $loggerFunc = null) {

        $this->apiURL     = ($isTest) ? 'https://apitest.myfatoorah.com' : 'https://api.myfatoorah.com';
        $this->apiKey     = $apiKey;
        $this->loggerObj  = $loggerObj;
        $this->loggerFunc = $loggerFunc;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    public function callAPI($url, $postFields, $orderId, $function, $request = 'POST') {
        //to prevent json_encode adding lots of decimal digits
        ini_set("precision", 14);
        ini_set("serialize_precision", -1);
        $fields = json_encode($postFields);

        $msgLog = "Order #$orderId ----- $function";

        if ($function != 'Direct Payment') {
            $this->log("$msgLog - Request: $fields");
        }

        //***************************************
        //call url
        //***************************************
        $curl = curl_init($url);

        curl_setopt_array($curl, array(
            CURLOPT_CUSTOMREQUEST  => $request,
            CURLOPT_POSTFIELDS     => $fields,
            CURLOPT_HTTPHEADER     => array("Authorization: Bearer $this->apiKey", 'Content-Type: application/json'),
            CURLOPT_RETURNTRANSFER => true,
        ));

        $res = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);


        //***************************************
        //check for errors
        //***************************************
        //example set a local ip to host apitest.myfatoorah.com
        if ($err) {
            $this->log("$msgLog - cURL Error: $err");
            throw new Exception($err);
        }

        $this->log("$msgLog - Response: $res");
        $json = json_decode($res);
        if ($request == "GET") {
            if (!($json) || (isset($json->Message))) {
                $message = isset($json->Message) ? $json->Message : '';
                $err     = $message . ' Kindly, review your Myfatoorah admin configuration due to a wrong entry.';
                $this->log("$msgLog - Error: $err");
                throw new Exception($err);
            }
            return $json;
        }
        if (!isset($json->IsSuccess) || $json->IsSuccess == null || $json->IsSuccess == false) {

            //check for the error insde the object Please tell the exact postion and dont use else
            if (isset($json->ValidationErrors)) {
                $err = implode(', ', array_column($json->ValidationErrors, 'Error'));
                /* $blogDatas = array_column($json->ValidationErrors, 'Error', 'Name');
                  $err_message = implode(', ', array_map(function ($k, $v) { return "$k: $v"; }, array_keys($blogDatas), array_values($blogDatas))); */
            } else if (isset($json->Data->ErrorMessage)) {
                $err = $json->Data->ErrorMessage;
            }

            //if not get the message. this is due that sometimes errors with ValidationErrors has Error value null so either get the "Name" key or get the "Message"
            //example {"IsSuccess":false,"Message":"Invalid data","ValidationErrors":[{"Name":"invoiceCreate.InvoiceItems","Error":""}],"Data":null}
            //example {"Message":"No HTTP resource was found that matches the request URI 'https://apitest.myfatoorah.com/v2/SendPayment222'.","MessageDetail":"No route providing a controller name was found to match request URI 'https://apitest.myfatoorah.com/v2/SendPayment222'"}
            if (empty($err)) {
                $err = (isset($json->Message)) ? $json->Message : (!empty($res) ? $res : 'Kindly, review your Myfatoorah admin configuration due to a wrong entry.');
            }

            $this->log("$msgLog - Error: $err");

            throw new Exception($err);
        }

        //***************************************
        //Success 
        //***************************************
        return $json;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /*
     * Matching regular expression pattern: ^(?:(\+)|(00)|(\\*)|())[0-9]{3,14}((\\#)|())$
     * if (!preg_match('/^(?:(\+)|(00)|(\\*)|())[0-9]{3,14}((\\#)|())$/iD', $inputString))
     * String length: inclusive between 0 and 11
     */
    public function getPhone($inputString) {

        //remove any arabic digit
        $newNumbers = range(0, 9);

        $persianDecimal = array('&#1776;', '&#1777;', '&#1778;', '&#1779;', '&#1780;', '&#1781;', '&#1782;', '&#1783;', '&#1784;', '&#1785;'); // 1. Persian HTML decimal
        $arabicDecimal  = array('&#1632;', '&#1633;', '&#1634;', '&#1635;', '&#1636;', '&#1637;', '&#1638;', '&#1639;', '&#1640;', '&#1641;'); // 2. Arabic HTML decimal
        $arabic         = array('٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'); // 3. Arabic Numeric
        $persian        = array('۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'); // 4. Persian Numeric

        $string0 = str_replace($persianDecimal, $newNumbers, $inputString);
        $string1 = str_replace($arabicDecimal, $newNumbers, $string0);
        $string2 = str_replace($arabic, $newNumbers, $string1);
        $string3 = str_replace($persian, $newNumbers, $string2);

        //Keep Only digits
        $string4 = preg_replace('/[^0-9]/', '', $string3);

        //remove 00 at start
        if (strpos($string4, '00') === 0) {
            $string4 = substr($string4, 2);
        }

        if (!$string4) {
            return ['', ''];
        }

        //check for the allowed length
        $len = strlen($string4);
        if ($len < 3 || $len > 14) {
            throw new Exception('Phone Number lenght must be between 3 to 14 digits');
        }

        //get the phone arr
        if (strlen(substr($string4, 3)) > 3) {
            return [
                substr($string4, 0, 3),
                substr($string4, 3)
            ];
        } else {
            return [
                '',
                $string4
            ];
        }
        ///end here with return $arr
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /*
     * need test
     * use $this->log('your msg here');
     */
    public function log($msg) {

        if (is_string($this->loggerObj)) {
            error_log(PHP_EOL . date('d.m.Y h:i:s') . ' - ' . $msg, 3, $this->loggerObj);
        } else if (method_exists($this->loggerObj, $this->loggerFunc)) {
            $this->loggerObj->{$this->loggerFunc}($msg);
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    function getWeightRate($unit) {

        if ($unit == 'kg') {
            $rate = 1; //kg is the default
        } else if ($unit == 'g') {
            $rate = 0.001;
        } else if ($unit == 'lbs') {
            $rate = 0.453592;
        } else if ($unit == 'oz') {
            $rate = 0.0283495;
        } else {
            throw new Exception('Weight must be in kg, g, lbs,or oz. Default is kg');
        }

        return $rate;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    function getDimensionRate($unit) {

        if ($unit == 'cm') {
            $rate = 1;
        } elseif ($unit == 'm') {
            $rate = 100;
        } else if ($unit == 'mm') {
            $rate = 0.1;
        } else if ($unit == 'in') {
            $rate = 2.54;
        } else if ($unit == 'yd') {
            $rate = 91.44;
        } else {
            throw new Exception('Dimension must be in cm, m, mm, in, or yd. Default is cm');
        }

        return $rate;
    }

}
