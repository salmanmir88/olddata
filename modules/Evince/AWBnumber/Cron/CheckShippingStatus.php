<?php

namespace Evince\AWBnumber\Cron;

class CheckShippingStatus {

    protected $orderCollectionFactory;
    protected $scopeConfig;
    protected $resourceConnection;
    protected $aramexShipmentStatusModel;
    protected $saeeShipmentStatusModel;

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory, 
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Evince\AWBnumber\Model\ShipmentStatus\AramexShipmentStatus $aramexShipmentStatusModel,
        \Evince\AWBnumber\Model\ShipmentStatus\SaeeShipmentStatus $saeeShipmentStatusModel
    ) {

        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;
        $this->aramexShipmentStatusModel = $aramexShipmentStatusModel;
        $this->saeeShipmentStatusModel = $saeeShipmentStatusModel;
    }

    public function execute() {

        try {
            $getShipmentStatus = $this->scopeConfig->getValue('awbshipment/order/status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $orders = $this->orderCollectionFactory->create()
                ->addAttributeToFilter('status', array('eq' => $getShipmentStatus ));
            
            foreach ($orders as $order)
            {
                $storeId = $order->getData('store_id');
                $shippingaddress = $order->getShippingAddress();
                $shippingCity = $shippingaddress->getData('city');
                //echo "<pre>"; var_dump($shippingCity); echo "</pre>";  exit;
//                
                switch ($storeId) {
                    case "1":
                        $getShippingMethod = $this->getShippingMethod($shippingCity);
                        break;
                    case "2":
                        $getShippingMethod = $this->getArabicShippingMethod($shippingCity);
                        break;
                }
                //echo "<pre>"; var_dump($getShippingMethod); echo "</pre>";  exit;
                
                switch ($getShippingMethod) {
                    case "aramex":
                        $this->aramexShipmentStatusModel->checkAramexShipmentStatus($order);
                        break;
//                    case "fetchr":
//                        $this->fetcrModel->CreateDropshipOrders($order);
//                        break;
                    case "saee":
                        $this->saeeShipmentStatusModel->checkSaeeShipmentStatus($order);
                        break;
                }
                
            }
            
            return;
        } catch (\Exception $e) {
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/shipment_cron_error.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            $logger->info($e->getMessage());
        }
    }
    
    public function getShippingMethod($city) {

        $connection = $this->resourceConnection->getConnection();
        $query = 'SELECT `courier` FROM `courier_manager` WHERE `city` = "' . $city . '"';
        //echo $query; exit;
        $shipping_method = $connection->fetchOne($query);
        return $shipping_method;
    }

    public function getArabicShippingMethod($city) {
        $_ar_connection = $this->resourceConnection->getConnection();
        $_ar_query = 'SELECT `courier` FROM `courier_manager` WHERE `city_arabic` = "' . $city . '"';
        $_ar_shipping_method = $_ar_connection->fetchOne($_ar_query);
        return $_ar_shipping_method;
    }

}
