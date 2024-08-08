<?php

namespace Evince\AWBnumber\Model;

class Fetchr extends \Magento\Framework\Model\AbstractModel {

    protected $_objectManager;
    protected $_messageManager;
    protected $_countryFactory;
    protected $_scopeConfig;
    protected $resourceConnection;
    protected $_awbnumberHelper;
    

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Evince\AWBnumber\Helper\Data $awbNumberHelper
    ) {
        $this->_objectManager = $objectManager;
        $this->_messageManager = $messageManager;
        $this->_countryFactory = $countryFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;
        $this->_awbnumberHelper = $awbNumberHelper;
    }
    
    public function CreateDropshipOrders($order) {
        
        $totalItemCount = 0;
        foreach ($order->getAllVisibleItems() as $item) {

            $totalItemCount = $totalItemCount + $item['qty_ordered'];

            //Hnadling Special characters in the items name
            $item['name'] = strtr($item['name'], array('"' => ' Inch ', '&' => ' And '));

            if ($item['product_type'] == 'bundle') {
                $product = $this->_objectManager->create('catalog/product')->load($item->getProductId());
                $parentSku = $product->getSku();
                $skuArray = explode($parentSku . '-', $item['sku']);
                $childSku = $skuArray[1];

                $itemArray[] = array(
                    'description' => $item['name'],
                    'sku' => $childSku,
                    'quantity' => intval($item['qty_ordered']),
                    'price_per_unit' => $item->getPriceInclTax(),
                );
            } elseif ($item['product_type'] == 'configurable') {
                $itemArray[] = array(
                    'description' => $item['name'],
                    'sku' => $item['sku'],
                    'quantity' => intval($item['qty_ordered']),
                    'price_per_unit' => $item->getPriceInclTax(),
                );
            } else {
                $itemArray[] = array(
                    'description' => $item['name'],
                    'sku' => $item['sku'],
                    'quantity' => intval($item['qty_ordered']),
                    'price_per_unit' => $item->getPriceInclTax(),
                );
            }
        }

        $address = $order->getShippingAddress()->getData();
        $customer_country = $this->_objectManager->create('\Magento\Directory\Model\Country')->load($address['country_id'])->getName();
        
        
        //Handling Special chars in the address
        foreach ($address as $key => $value) {
            $address[$key] = strtr($address[$key], array('"' => ' ', '&' => ' And '));
        }
        
        $addressId = $this->_awbnumberHelper->getFetchrPickupAddressId();
        $description = $this->getItemDescription($order);
        $_description =  implode(" ",$description);
        
        $payload = array(
            'client_address_id' => $addressId,
            'data' => array(
                array(
                    'order_reference' => $order->getIncrementId(),
                    'name' => $address['firstname'] . ' ' . $address['lastname'],
                    'email' => $order->getCustomerEmail(),
                    'phone_number' => $address['telephone'],
                    'address' => $address['street'],
                    'receiver_city' => $address['city'],
                    'receiver_country' => $customer_country,
                    'payment_type' => 'Credit Card',
                    'total_amount' => $order->getGrandTotal(),
                    'description' => $_description,
                    'bag_count' => $this->_scopeConfig->getValue('carriers/fetchr/productbagcount') ? $totalItemCount : 1,
                    'items' => $itemArray,
                ),
            ),
        );
        
        $token = $this->_awbnumberHelper->getFetchrToken();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://xapi.stag.fetchr.us/order');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            'Authorization: Bearer '.$token,
        ));
        $response = curl_exec($ch);
        curl_close($ch);
        $fetchrData = json_decode($response);
        
        if($fetchrData->data[0]->status == "success")
        {
            $awbLink = $fetchrData->data[0]->awb_link;
            $sql = "UPDATE `sales_order_grid` SET `awb_link` = '".$awbLink."'  WHERE `increment_id` = ".$order->getIncrementId()." ";
            $this->resourceConnection->getConnection()->query($sql);
            $message =  $this->_messageManager->addSuccess(__($fetchrData->data[0]->message));
        }
        
        if($fetchrData->data[0]->status == "error")
        {
            $message =  $this->_messageManager->addError(__($fetchrData->data[0]->message));
            
        }
        return $message;
        
    }
    public function getItemDescription($order)
    {
      $items = $order->getAllItems();
        foreach ($items as $item) {
            if(count($items) > 1)
            {
                $itemDescription[] = $item->getName().': '.intval($item->getQtyOrdered()).',';
            }
            else
            {
                $itemDescription[] = $item->getName().': '.intval($item->getQtyOrdered());
            }
        }
        return $itemDescription;
    }

}
