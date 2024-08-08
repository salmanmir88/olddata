<?php

namespace Saee\ShipmentMethod\Helper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\HTTP\Client\Curl;



/**
 * Class SaeeUtils
 * @package Saee\ShipmentMethod\Helper
 */
class SaeeUtils extends AbstractHelper
{

    const SAEE_KEY = 'carriers/saeeShipping/key';
    const SAEE_URL = 'carriers/saeeShipping/url';
    const PICKUP_ADDRESS_ID = 'carriers/saeeShipping/pickup_address_id';


    /**
     * @var string
     */
    protected $storeScope;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Curl
     */
    protected $_saeeCurl;


    /**
     * SaeeUtils constructor.
     * @param Context $context
     * @param Curl $saeeCurl
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(Context $context,
                                Curl $saeeCurl,
                                ScopeConfigInterface $scopeConfig) {

        parent::__construct($context);

        $this->scopeConfig = $scopeConfig;
        $this->storeScope = ScopeInterface::SCOPE_STORE;
        $this->_saeeCurl = $saeeCurl;
        $this->_saeeCurl->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->_saeeCurl->setOption(CURLOPT_SSL_VERIFYPEER, false); // this should be set to true in production

    }

    /**
     * @return string
     */
    public function getSaeeKey(){
        return $this->scopeConfig->getValue(self::SAEE_KEY, $this->storeScope);
    }

    /**
     * @return string
     */
    public function getSaeeUrl(){
        return $this->scopeConfig->getValue(self::SAEE_URL, $this->storeScope);
    }

    /**
     * @return mixed
     */
    public function getPickupAddressId(){
        return $this->scopeConfig->getValue(self::PICKUP_ADDRESS_ID, $this->storeScope);

    }

    /**
     * @param string $orderID
     * @return mixed
     */
    public function getSaeeWaybill(string $orderID){
        $objectManager = ObjectManager::getInstance();
        $model = $objectManager->create('\Saee\ShipmentMethod\Model\DbData');
        $collection = $model->getCollection();
        $collection->addFieldToFilter('order_id', $orderID);
        return $collection->getFirstItem()->getWaybill();
            }


    /***
     * @param string $Url
     * @param string $method
     * @param array $data
     * @return string
     */
    public function saeeCurlExec(string $Url,
                                 string $method,
                                 array $data=[]
    ){

        if ($method == "POST"){
            $this->_saeeCurl->post($Url, $data);
        }

        if ($method == "GET"){
            $this->_saeeCurl->get($Url);
        }

        $this->_logger->info("CURL RESULTS    " .$this->_saeeCurl->getBody());
        return $this->_saeeCurl->getBody();
    }
}
