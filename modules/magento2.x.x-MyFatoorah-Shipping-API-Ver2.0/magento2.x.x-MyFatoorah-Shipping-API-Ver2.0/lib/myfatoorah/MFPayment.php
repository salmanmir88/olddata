<?php

require_once 'MFApiV2.php';

class MFPayment extends MFApiV2 {

    /**
     * list avail Payment Gateways.
     */
    public function getVendorGateways($invoiceValue = 0, $displayCurrencyIso = '') {

        $postFields = [
            'InvoiceAmount' => $invoiceValue,
            'CurrencyIso'   => $displayCurrencyIso,
        ];

        $json = $this->callAPI("$this->apiURL/v2/InitiatePayment", $postFields, null, 'Initiate Payment');

        return $json->Data->PaymentMethods;
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------q
    public function getVendorGatewaysByType($isDirect = false) {

        try {
            $gateways = $this->getVendorGateways();
        } catch (Exception $ex) {
            return array();
        }

        foreach ($gateways as $g) {
            if ($g->IsDirectPayment) {
                $directMethods[] = $g;
            } else {
                $normalMethods[] = $g;
            }
        }

        return ($isDirect && isset($directMethods)) ? $directMethods : ((!$isDirect && isset($normalMethods)) ? $normalMethods : []);
    }

    //---------------------------------------------------------------------------------------------------------------------------------------------------
    public function getPaymentMethod($gateway, $gatewayType = 'PaymentMethodId', $invoiceValue = 0, $displayCurrencyIso = '') {

        $paymentMethods = $this->getVendorGateways($invoiceValue, $displayCurrencyIso);

        foreach ($paymentMethods as $method) {
            if ($method->$gatewayType == $gateway) {
                $pm = $method;
                break;
            }
        }

        if (!isset($pm)) {
            throw new Exception('Please contact Account Manager to enable the used payment method in your account');
        }

        if ($this->isDirectPayment && !$pm->IsDirectPayment) {
            throw new Exception($pm->PaymentMethodEn . ' Direct Payment Method is not activated. Kindly, contact your MyFatoorah account manager or sales representative to activate it.');
        }

        return $pm;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    public function getInvoiceURL($orderId, $curlData, $gateway = 'myfatoorah') {

        $this->log('----------------------------------------------------------------------------------------------------------------------------------');

        $this->isDirectPayment = false;

        if ($gateway == 'myfatoorah') {
            return $this->sendPayment($orderId, $curlData);
        } else {
            return $this->excutePayment($orderId, $curlData, $gateway);
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    private function excutePayment($orderId, $curlData, $gatewayId) {

        $curlData['PaymentMethodId'] = $gatewayId;

        $json = $this->callAPI("$this->apiURL/v2/ExecutePayment", $curlData, $orderId, 'Excute Payment'); //__FUNCTION__

        return ['invoiceURL' => $json->Data->PaymentURL, 'invoiceId' => $json->Data->InvoiceId];
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    private function sendPayment($orderId, $curlData) {

        $curlData['NotificationOption'] = 'Lnk';

        $json = $this->callAPI("$this->apiURL/v2/SendPayment", $curlData, $orderId, 'Send Payment');

        return ['invoiceURL' => $json->Data->InvoiceURL, 'invoiceId' => $json->Data->InvoiceId];
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    public function directPayment($orderId, $curlData, $gateway, $cardInfo) {

        $this->log('----------------------------------------------------------------------------------------------------------------------------------');

        $this->isDirectPayment = true;

        $data = $this->excutePayment($orderId, $curlData, $gateway);

        $json = $this->callAPI($data['invoiceURL'], $cardInfo, $orderId, 'Direct Payment'); //__FUNCTION__
        return ['invoiceURL' => $json->Data->PaymentURL, 'invoiceId' => $json->Data->InvoiceId];
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    public function getPaymentStatus($keyId, $KeyType, $orderId = null) {

        $curlData = ['Key' => $keyId, 'KeyType' => $KeyType];

        $json = $this->callAPI("$this->apiURL/v2/GetPaymentStatus", $curlData, $orderId, 'Get Payment Status');


        if ($orderId && $json->Data->CustomerReference != $orderId) {
            throw new Exception('Trying to call data of another order');
        } else if ($json->Data->InvoiceStatus == 'DuplicatePayment') {
            throw new Exception('Duplicate Payment', 3); //success with Duplicate
        }

        if ($KeyType == 'PaymentId') {
            foreach ($json->Data->InvoiceTransactions as $transaction) {
                if ($transaction->PaymentId == $keyId && $transaction->Error) {
                    throw new Exception('Failed with Error (' . $transaction->Error . ')', 1); //faild order
                }
            }
        }

        if ($json->Data->InvoiceStatus != 'Paid') {

            //------------------
            //case 1:
            $lastInvoiceTransactions = end($json->Data->InvoiceTransactions);
            if ($lastInvoiceTransactions && $lastInvoiceTransactions->Error) {
                throw new Exception('Failed with Error (' . $lastInvoiceTransactions->Error . ')', 1); //faild order
            }

            //------------------
            //case 2:
            //all myfatoorah gateway is set to Asia/Kuwait
            $ExpiryDate  = new \DateTime($json->Data->ExpiryDate, new \DateTimeZone('Asia/Kuwait'));
            $ExpiryDate->modify('+1 day'); ///????????????$ExpiryDate without any hour so for i added the 1 day just in case. this should be changed after adding the tome to the expire date
            $currentDate = new \DateTime('now', new \DateTimeZone('Asia/Kuwait'));

            if ($ExpiryDate < $currentDate) {
                throw new Exception('Invoice is expired since: ' . $ExpiryDate->format('Y-m-d'), 2); //cancelled order
            }

            //------------------
            //case 3:
            //payment is pending .. user has not paid yet and the invoice is not expired
            throw new Exception('Payment is pending');
        }

        return $json;
    }

    //-----------------------------------------------------------------------------------------------------------------------------------------

    public function refund($paymentId, $amount, $currencyCode, $reason, $orderId) {

        $rate = $this->getCurrencyRate($currencyCode);
        $url  = "$this->apiURL/v2/MakeRefund";

        $postFields = array(
            'KeyType'                 => 'PaymentId',
            'Key'                     => $paymentId,
            'RefundChargeOnCustomer'  => false,
            'ServiceChargeOnCustomer' => false,
            'Amount'                  => $amount / $rate,
            'Comment'                 => $reason,
        );

        return $this->callAPI($url, $postFields, $orderId, 'Make Refund');
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    public function cancelSubscribtion($orderId, $recurringId) {

        $url = $this->apiURL . '/v2/CancelRecurringPayment?recurringId=' . urlencode($recurringId);

        return $this->callAPI($url, '', $orderId, 'Cancel Subscribtion'); //__FUNCTION__
    }

}
