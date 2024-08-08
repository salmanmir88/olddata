<?php

namespace MyFatoorah\ShippingDHL\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Config;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Psr\Log\LoggerInterface;

class ShippingDHL extends AbstractCarrier implements CarrierInterface {

    /**

     * Carrier's code
     *
     * @var string
     */
    protected $_code = 'myfatoorah_shippingDHL';

    /**
     * Whether this carrier has fixed rates calculation
     *
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var MethodFactory
     */
    protected $_rateMethodFactory;
    protected $apiKey;
    protected $apiURL;
    protected $log;

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory $rateErrorFactory
     * @param LoggerInterface $logger
     * @param ResultFactory $rateResultFactory
     * @param MethodFactory $rateMethodFactory
     * @param array $data
     */
    public function __construct(
            ScopeConfigInterface $scopeConfig,
            ErrorFactory $rateErrorFactory,
            LoggerInterface $logger,
            ResultFactory $rateResultFactory,
            MethodFactory $rateMethodFactory,
            array $data = []
    ) {
        $this->objectManager = \Magento\Framework\App\ObjectManager::getInstance();
//        $this->checkoutSession = $this->objectManager->get('Magento\Checkout\Model\Session');

        $this->apiKey    = $this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('carriers/myfatoorah_shippingDHL/apiKey');
        $this->isTesting = $this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('carriers/myfatoorah_shippingDHL/isTesting');

        $this->apiURL = ($this->isTesting) ? 'https://apitest.myfatoorah.com/' : 'https://api.myfatoorah.com';

        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;

        $writer    = new \Zend\Log\Writer\Stream(BP . '/var/log/myfatoorah_shippingDHL.log');
        $this->log = new \Zend\Log\Logger();
        $this->log->addWriter($writer);
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Generates list of allowed carrier`s shipping methods
     * Displays on cart price rules page
     *
     * @return array
     * @api
     */
    public function getAllowedMethods() {
        return [$this->getCarrierCode() => __($this->getConfigData('name'))];
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Collect and get rates for storefront
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @param RateRequest $request
     * @return DataObject|bool|null
     * @api
     */
    public function collectRates(RateRequest $request) {
        /**
         * Make sure that Shipping method is enabled
         */
        if (!$this->isActive()) {
            return false;
        }

        $this->log->info("-----------------------------------------------------------------------------------------------------");

        $this->currency     = $this->objectManager->create('Magento\Store\Model\StoreManagerInterface')->getStore()->getCurrentCurrency()->getCode();
        $this->currencyRate = $this->getCurrencyRate();

        /** @var \Magento\Checkout\Model\Session $checkoutSession */
        /** @var \Magento\Quote\Model\Quote $quote */
//        $quote = $this->checkoutSession->getQuote();

        /** @var \Magento\Quote\Model\Quote\Item[] $items */
//        $items = $quote->getAllVisibleItems();
        $items = $request->getAllItems();

        $weightUnit = $this->_scopeConfig->getValue('general/locale/weight_unit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $weightRate = $this->getWeightRate($weightUnit);

        $invoiceItemsArr = array();
        foreach ($items as $item) {

            //get dimensions
            $productId = $item->getProductId();
            $product   = $this->objectManager->create('Magento\Catalog\Model\Product')->load($productId);

            $width  = $product->getData('width');
            $height = $product->getData('height');
            $depth  = $product->getData('depth');


            //get weight
            $weight = $item->getWeight() * $weightRate;

            if (!$weight || !$width || !$height || !$depth) {
                $msgErr = 'Kindly, contact the site admin to set dimensions for ' . $item->getName();
                $this->log->info("DimensionsAdd - Error: $msgErr");
                throw new \Magento\Framework\Exception\LocalizedException(__($msgErr));
            }

            $invoiceItemsArr[] = array(
                'ProductName' => $item->getName(),
                "Description" => $item->getName(),
                'weight'      => $weight,
                'Width'       => $width,
                'Height'      => $height,
                'Depth'       => $depth,
                'Quantity'    => $item->getQty(),
                'UnitPrice'   => $item->getPrice(),
            );
        }

        
        $curlData = [
            'ShippingMethod' => 1,
            'Items'          => $invoiceItemsArr,
            'CityName'       => $request->getDestCity(),
            'PostalCode'     => $request->getDestPostcode(),
            'CountryCode'    => $request->getDestCountryId()
        ];

        $url = "$this->apiURL/v2/CalculateShippingCharge";

        $json = $this->callAPI($url, $curlData, 'CalculateShippingCharge');


        $shippingAmount = ($json->Data->Fees * $this->currencyRate);
        if ($shippingAmount) {

            $method = $this->_rateMethodFactory->create();
            /**
             * Set carrier's method data
             */
            $method->setCarrier($this->getCarrierCode());
            $method->setCarrierTitle($this->getConfigData('title'));
            /**
             * Displayed as shipping method under Carrier
             */
            $method->setMethod($this->getCarrierCode());
            $method->setMethodTitle($this->getConfigData('name'));

            $method->setPrice($shippingAmount);
            $method->setCost($shippingAmount);

            /** @var \Magento\Shipping\Model\Rate\Result $result */
            $result = $this->_rateResultFactory->create();
            $result->append($method);
            return $result;
        }
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
    /*
     *   check CountryAndCurrency 
     */

    function getCurrencyRate() {
        $url  = "$this->apiURL/v2/GetCurrenciesExchangeList";
        $curl = curl_init($url);

        curl_setopt_array($curl, array(
            CURLOPT_HTTPHEADER     => array("Authorization: Bearer $this->apiKey", 'Content-Type: application/json'),
            CURLOPT_RETURNTRANSFER => true,
        ));

        $res      = curl_exec($curl);
        $err      = curl_error($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        curl_close($curl);

        if ($err || $httpcode != 200) {
            $msg = $err ? "cURL Error: $err" : 'Kindly, review your Myfatoorah admin configuration due to a wrong entry.';
            $this->log->info("currencyRate - Error: $msg");
            throw new \Magento\Framework\Exception\LocalizedException(__($msg));
        }


        $json = json_decode($res);
        foreach ($json as $value) {
            if ($this->currency == $value->Text) {
                $this->log->info("currencyRate: $value->Value");
                return $value->Value;
            }
        }

        $err = 'The site cuurency is not supported by Myfatoorah';
        $this->log->info("CurrencyRate - Error: $err");
        throw new \Magento\Framework\Exception\LocalizedException(__($err));
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
    protected function callAPI($url, $postFields, $function) {

        $fields = json_encode($postFields);

        $this->log->info("$function - Request: $fields");


        //***************************************
        //call url
        //***************************************
        $curl = curl_init($url);

        curl_setopt_array($curl, array(
            CURLOPT_CUSTOMREQUEST  => 'POST',
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
            $this->log->info("$function - cURL Error: $err");
            throw new \Magento\Framework\Exception\LocalizedException(__($err));
        }

        $this->log->info("$function - Response: $res");

        $json = json_decode($res);
        if (!isset($json->IsSuccess) || $json->IsSuccess == null || $json->IsSuccess == false) {

            //check for the error insde the object Please tell the exact postion and dont use else
            if (isset($json->FieldsErrors)) {
//                $err = implode(', ', array_column($json->ValidationErrors, 'Error'));
                $blogDatas = array_column($json->FieldsErrors, 'Error', 'Name');
                $err       = implode(', ', array_map(function ($k, $v) {
                            return "$k: $v";
                        }, array_keys($blogDatas), array_values($blogDatas)));
            } else if (isset($json->Data->ErrorMessage)) {
                $err = $json->Data->ErrorMessage;
            }

            //if not get the message. this is due that sometimes errors with ValidationErrors has Error value null so either get the "Name" key or get the "Message"
            //example {"IsSuccess":false,"Message":"Invalid data","ValidationErrors":[{"Name":"invoiceCreate.InvoiceItems","Error":""}],"Data":null}
            //example {"Message":"No HTTP resource was found that matches the request URI 'https://apitest.myfatoorah.com/v2/SendPayment222'.","MessageDetail":"No route providing a controller name was found to match request URI 'https://apitest.myfatoorah.com/v2/SendPayment222'"}
            if (empty($err)) {
                $err = (isset($json->Message)) ? $json->Message : (!empty($res) ? $res : __('Kindly, review your Myfatoorah admin configuration due to a wrong entry.'));
            }

            $this->log->info("$function - Error: $err");
            throw new \Magento\Framework\Exception\LocalizedException(__($err));
        }


        //***************************************
        //Success 
        //***************************************
        return $json;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get the rate that will convert the given weight unit to MyFatoorah default weight unit.
     * 
     * @param string        $unit It is the weight unit used. Weight must be in kg, g, lbs, or oz. Default is kg.
     * @return real         The conversion rate that will convert the given unit into the kg. 
     * @throws Exception    Throw exception if the input unit is not support. Weight must be in kg, g, lbs, or oz. Default is kg.
     */
    public function getWeightRate($unit) {

        $unit1 = strtolower($unit);
        if ($unit1 == 'kg') {
            $rate = 1; //kg is the default
        } else if ($unit1 == 'g') {
            $rate = 0.001;
        } else if ($unit1 == 'lbs') {
            $rate = 0.453592;
        } else if ($unit1 == 'oz') {
            $rate = 0.0283495;
        } else {
            $err = 'Weight must be in kg, g, lbs, or oz. Default is kg';
            $this->log->info("WeightRate - Error: ");
            throw new \Magento\Framework\Exception\LocalizedException(__($err));
        }

        return $rate;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------

    /**
     * Get the rate that will convert the given dimension unit to MyFatoorah default dimension unit.
     * 
     * @param string        $unit It is the dimension unit used in width, hight, or depth. Dimension must be in cm, m, mm, in, or yd. Default is cm.
     * @return real         The conversion rate that will convert the given unit into the cm.
     * @throws Exception    Throw exception if the input unit is not support. Dimension must be in cm, m, mm, in, or yd. Default is cm.
     */
    public function getDimensionRate($unit) {

        $unit1 = strtolower($unit);
        if ($unit1 == 'cm') {
            $rate = 1; //cm is the default
        } elseif ($unit1 == 'm') {
            $rate = 100;
        } else if ($unit1 == 'mm') {
            $rate = 0.1;
        } else if ($unit1 == 'in') {
            $rate = 2.54;
        } else if ($unit1 == 'yd') {
            $rate = 91.44;
        } else {
            $err = 'Dimension must be in cm, m, mm, in, or yd. Default is cm';
            $this->log->info("DimensionRate - Error: $err");
            throw new \Magento\Framework\Exception\LocalizedException(__($err));
        }

        return $rate;
    }

//-----------------------------------------------------------------------------------------------------------------------------------------
}
