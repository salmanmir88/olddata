<?php
/**
 * Fetchr
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * https://fetchr.zendesk.com/hc/en-us/categories/200522821-Downloads
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to ws@fetchr.us so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Fetchr Magento Extension to newer
 * versions in the future. If you wish to customize Fetchr Magento Extension (Fetchr Shipping) for your
 * needs please refer to http://www.fetchr.us for more information.
 *
 * @author     Danish Kamal, Feiran Wang
 * @package    Fetchr Shipping
 * Used in creating options for fulfilment|delivery config value selection
 * @copyright  Copyright (c) 2018 Fetchr (http://www.fetchr.us)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Fetchr_Shipping_Model_Carrier extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements \Magento\Shipping\Model\Carrier\CarrierInterface
{
  protected $_code = 'fetchr';
  protected $_rateResultFactory;
  protected $_rateMethodFactory;

  public function __construct(\Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
                              \Psr\Log\LoggerInterface $logger, array $data = [], 
                              \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
                              \Magento\Shipping\Model\Rate\ResultFactory $resultFactory,
                              \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $methodFactory)
   {
      $this->_rateResultFactory = $resultFactory;
      $this->_rateMethodFactory = $methodFactory;
      parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

  public function collectRates(\Magento\Quote\Model\Quote\Address\RateRequest $request)
  {
    if (!$this->_scopeConfig->getValue('carriers/'.$this->_code.'/active')) {
        return false;
    }
    $handling = $this->_scopeConfig->getValue('carriers/'.$this->_code.'/handling');
    $method   = $this->_rateMethodFactory->create();
    $result   = $this->_rateResultFactory->create();

    $allowedMethods = $this->getAllowedMethods();
    $allowedMethods = explode(',', $allowedMethods);

    if(count($allowedMethods) == 1){

      $methodName = $allowedMethods[0];
      if($methodName == 'next_day'){
        $result->append($this->_getStandardRate());
      }else{
        $result->append($this->_getExpressRate());
      }

    }else{
      $result->append($this->_getStandardRate());
      $result->append($this->_getExpressRate());
    }

    return $result;
  }

  public function getAllowedMethods()
  {

    return $this->getConfigData('shippingoption');
  }

  protected function _getStandardRate()
  {
    $rate = $this->_rateMethodFactory->create();

    $rate->setCarrier($this->_code);
    $rate->setCarrierTitle($this->getConfigData('title'));
    $rate->setMethod('next_day');
    $rate->setMethodTitle('Next Day Delivery');
    $rate->setPrice($this->getConfigData('nextdaydeliveryrate'));
    $rate->setCost('0');

    return $rate;
  }

  protected function _getExpressRate()
  {
    $rate = $this->_rateMethodFactory->create();

    $rate->setCarrier($this->_code);
    $rate->setCarrierTitle($this->getConfigData('title'));
    $rate->setMethod('same_day');
    $rate->setMethodTitle('Same Day Delivery');
    $rate->setPrice($this->getConfigData('samedaydeliveryrate'));
    $rate->setCost('0');

    return $rate;
  }

  public function isTrackingAvailable()
  {
    return true;
  }

  public function getTrackingInfo($tracking)
  {
    $track = $this->_objectManager->create('shipping/tracking_result_status');
    $track->setUrl('http://track.fetchr.us/track/' . $tracking)
          ->setTracking($tracking)
          ->setCarrierTitle($this->getConfigData('name'));
    return $track;
  }
}
