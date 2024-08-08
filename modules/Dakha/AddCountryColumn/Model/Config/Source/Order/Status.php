<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Order Statuses source model
 */
namespace Dakha\AddCountryColumn\Model\Config\Source\Order;

/**
 * Class Status
 * @api
 * @since 100.0.2
 */
class Status implements \Magento\Framework\Option\ArrayInterface
{

   /**
    * @var \Magento\Framework\App\Config\ScopeConfigInterface
    */
   protected $scopeConfig;
   
   /**
    * @var \Magento\Framework\Serialize\Serializer\Json
    */
   protected $serialize;

   /**
    * @var \Magento\Sales\Model\Config\Source\Order\Status
    */
   protected $status; 
   /**
    * Custom work order status config path
    */
   const XML_PATH_CUSTOMWORK_GENERAL_ORDER_STATUS = 'customwork/general/order_status';

   /**
    * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    * @param \Magento\Framework\Serialize\Serializer\Json $serialize
    * @param \Magento\Sales\Model\Config\Source\Order\Status $status
    */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Serialize\Serializer\Json $serialize,
        \Magento\Sales\Model\Config\Source\Order\Status $status
      )
    {
       $this->scopeConfig = $scopeConfig;
       $this->serialize = $serialize;
       $this->status =$status;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $orderStatuConfig = $this->scopeConfig->getValue(self::XML_PATH_CUSTOMWORK_GENERAL_ORDER_STATUS, $storeScope);
        $unserializedata = $this->serialize->unserialize($orderStatuConfig);
        $statusData = $this->status->toOptionArray();
        $configStatusArr = [];
        foreach($statusData as $configStatus)
        {
          $configStatusArr[$configStatus['value']] = $configStatus['label']; 
        }
        
        $optionArr = [['value' => '', 'label' => __('-- Please Select --')]];
        foreach($unserializedata as $key => $row)
        {
            if(isset($configStatusArr[$row['order_status']])){
             $optionArr[] = ['value' => $row['order_status'], 'label' => $configStatusArr[$row['order_status']]];
            }
        }
        return $optionArr;
    }
}
