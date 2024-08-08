<?php

require_once 'MFApiV2.php';

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class MFShipping extends MFApiV2 {
    /*
     * get MyFatoorah Shipping Countries 
     */

    function getShippingCountries() {
        $url  = "$this->apiURL/v2/GetCountries";
        $json = $this->callAPI($url, null, null, 'Get Countries', 'GET');
        return $json;
    }

    /**
     * get Shipping Cities
     * */
    function getShippingCities($method, $countryCode, $searchValue = null) {
        $endPoint = 'GetCities?shippingMethod=' . $method . '&countryCode=' . $countryCode . '&searchValue=' . $searchValue;
        $url      = "$this->apiURL/v2/" . $endPoint;
        $json     = $this->callAPI($url, null, null, 'Get Cities - Country : ' . $countryCode, 'GET');
        return $json;
    }

    /**
     * get Currency Rate
     * */
    function getCurrencyRate($currency) {
        $url  = "$this->apiURL/v2/GetCurrenciesExchangeList";
        $json = $this->callAPI($url, array(), null, 'Get Currencies Exchange List ', 'GET');
        foreach (($json) as $value) {
            if ($value->Text == $currency) {
                return $value->Value;
            }
        }
        throw new Exception('The selected currency is not supported by MyFatoorah');
    }

    /**
     * Calculate Shipping Charge
     * */
    function calculateShippingCharge($curlData) {
        $url  = "$this->apiURL/v2/CalculateShippingCharge";
        $json = $this->callAPI($url, $curlData, null, 'Calculate Shipping Charge');
        return $json;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
}
