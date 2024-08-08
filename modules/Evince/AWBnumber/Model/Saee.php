<?php

namespace Evince\AWBnumber\Model;

class Saee extends \Magento\Framework\Model\AbstractModel {

    CONST CREATE_ORDER_NEW = '/deliveryrequest/new';

    protected $messageManager;
    protected $resourceConnection;
    protected $saeeHelper;
    protected $orderRepository;
    protected $convertOrder;
    protected $shipmentNotifier;
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Saee\ShipmentMethod\Helper\SaeeUtils $saeeHelper,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\Convert\Order $convertOrder,
        \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->messageManager = $messageManager;
        $this->resourceConnection = $resourceConnection;
        $this->saeeHelper = $saeeHelper;
        $this->orderRepository = $orderRepository;
        $this->convertOrder = $convertOrder;
        $this->shipmentNotifier = $shipmentNotifier;
        $this->scopeConfig = $scopeConfig;
    }

    public function saeeAwbNumber($order) {

        try {

            $address = $order->getShippingAddress()->getData();
            
            // EVINCE CHECK COD OR PREPAID
            $isOffline = $order->getPayment()->getMethodInstance()->isOffline();
 
            if ($isOffline) {
                // OFFLINE PAYMENT METHOD
                $cashOnDelivery = $order->getGrandTotal();
            } 
            else 
            {
                // ONLINE PAYMENT METHOD
                $cashOnDelivery = 0;
            }
            
            
            
            //Handling Special chars in the address
            foreach ($address as $key => $value) {
                $address[$key] = strtr($address[$key], array('"' => ' ', '&' => ' And '));
            }
            $totalWeight = $this->getTotalWeight($order);
            $totalQty = $this->getTotalQty($order);
            
            
            $orderStoreId = $order->getData('store_id');
            if($orderStoreId == "1")
            {
                // English Store View
                $data = json_encode(array(
                    "secret" => $this->saeeHelper->getSaeeKey(),
                    "ordernumber" => $order->getIncrementId(),
                    "cashondelivery" => $cashOnDelivery,
                    "name" => $address['firstname'] . ' ' . $address['lastname'],
                    "mobile" => $address['telephone'],
                    "streetaddress" => $address['street'],
                    "district" => "",
                    "streetaddress2" => "",
                    "city" => $address['city'],
                    "state" => "",
                    "zipcode" => "",
                    "weight" => $totalWeight,
                    "quantity" => 1,
                    "description" => ""
                ));
            }
            else
            {
                // Arabic Store View
                $data = json_encode(array(
                    "secret" => $this->saeeHelper->getSaeeKey(),
                    "ordernumber" => $order->getIncrementId(),
                    "cashondelivery" => $cashOnDelivery,
                    "name" => $address['firstname'] . ' ' . $address['lastname'],
                    "mobile" => $address['telephone'],
                    "streetaddress" => $address['street'],
                    "district" => "",
                    "streetaddress2" => "",
                    "city" => $this->getCityNameInEng($address['city']),
                    "state" => "",
                    "zipcode" => "",
                    "weight" => $totalWeight,
                    "quantity" => 1,
                    "description" => ""
                ));
            }
                
            $postUrl = $this->saeeHelper->getSaeeUrl() . self::CREATE_ORDER_NEW;

            $headers = array(
                'Content-Type: application/json; charset=utf-8'
            );
            $ch = curl_init($postUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $result = curl_exec($ch);
            curl_close($ch);
            $response=json_decode($result, true);
            //echo "<pre>"; print_r($response); echo "</pre>"; exit;
            if($response['success'] == true)
            {
                $wayBillNumber = $response['waybill'];
                
                $downloadlink = 'https://corporate.saeex.com/deliveryrequest/printsticker/pdf/'.$wayBillNumber;
                $sql = "UPDATE `sales_order_grid` SET `awb_link` = '".$downloadlink."'  WHERE `increment_id` = ".$order->getIncrementId()." ";
                $this->resourceConnection->getConnection()->query($sql);
                
                // update sales_order_grid
                $updateOrderGridTable = "UPDATE `sales_order_grid` SET `aramex_waybill_number` = '".$wayBillNumber."'  WHERE `increment_id` = ".$order->getIncrementId()." ";
                $this->resourceConnection->getConnection()->query($updateOrderGridTable);
            
                // update sales_order
                $updateOrderTable = "UPDATE `sales_order` SET `aramex_waybill_number` = '".$wayBillNumber."'  WHERE `increment_id` = ".$order->getIncrementId()." ";
                $this->resourceConnection->getConnection()->query($updateOrderTable);

                //$this->createShipment($order,$wayBillNumber);
                
                $message =  $this->messageManager->addSuccess(__($response['message']));
            }
            else
            {
                $message =  $this->messageManager->addError(__($response['error']));
            }
            
            return $message;
            
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    public function getTotalWeight($order) {
        $weight = 0;
        foreach ($order->getAllVisibleItems() as $item) {
            if ($item->getWeight() != 0) {
                $weight += ($item->getWeight() * $item->getQtyOrdered());
            } else {
                $weight += (0.5 * $item->getQtyOrdered());
            }
            return $weight;
        }
    }
    
    public function getTotalQty($order)
    {
        $orderItems = $order->getAllItems();
        $total_qty = 0;
        foreach ($orderItems as $item)
        {
             $total_qty = $total_qty + $item->getQtyOrdered();
        }
        return $total_qty;
    }
    
    public function getCityNameInEng($arcity)
    {
        $connection = $this->resourceConnection->getConnection();
        $query = "SELECT `city` FROM `courier_manager` WHERE `city_arabic` LIKE '%".$arcity."%' ";
        $shipping_method = $connection->fetchOne($query);
        return $shipping_method;
        
    }
    
    public function createShipment($order,$wayBillNumber)
    {
        
    
        $order = $this->orderRepository->get($order->getId());
 
        // to check order can ship or not
        if (!$order->canShip()) {
            throw new \Magento\Framework\Exception\LocalizedException(__('You cant create the Shipment of this order.') );
        }
 
        $orderShipment = $this->convertOrder->toShipment($order);
 
        foreach ($order->getAllItems() AS $orderItem) {
 
            // Check virtual item and item Quantity
            if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
               continue;
            }
 
            $qty = $orderItem->getQtyToShip();
            $shipmentItem = $this->convertOrder->itemToShipmentItem($orderItem)->setQty($qty);
 
            $orderShipment->addItem($shipmentItem);
        }
 
        $orderShipment->register();
        //$orderShipment->getOrder()->setIsInProcess(true);
        $getShipmentStatus = $this->scopeConfig->getValue('awbshipment/order/status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $objectManager1 = \Magento\Framework\App\ObjectManager::getInstance();
        $orderStatus = $objectManager1->create('\Magento\Sales\Model\Order')->load($order->getId()); 
        $orderStatus->setState($getShipmentStatus)->setStatus($getShipmentStatus);
        $orderStatus->save();
        try {
 
            // Save created Order Shipment
            $orderShipment->save();
            $orderShipment->getOrder()->save();
 
            //Save AWB number 
            $data = array(
                'carrier_code' => 'custom',
                'title' => 'Saee',
                'number' => $wayBillNumber, // Replace with your tracking number
            );

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $track = $objectManager->create('Magento\Sales\Model\Order\Shipment\TrackFactory')->create()->addData($data);
            $orderShipment->addTrack($track)->save();
            
            
            // Send Shipment Email
            $this->shipmentNotifier->notify($orderShipment);
            $orderShipment->save();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
    }

}
