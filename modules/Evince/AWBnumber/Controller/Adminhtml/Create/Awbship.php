<?php

namespace Evince\AWBnumber\Controller\Adminhtml\Create;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Filesystem\DirectoryList;

class Awbship extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction {

    protected $dateTime;
    protected $fileFactory;
    protected $orderManagement;
    protected $resourceConnection;
    protected $aramexModel;
    protected $fetcrModel;
    protected $saeeModel;
    protected $convertOrder;
    protected $shipmentNotifier;
    protected $scopeConfig;
    protected $shipmentLoader;
    protected $tracking;

    public function __construct(
        \Magento\Backend\App\Action\Context $context, 
        \Magento\Ui\Component\MassAction\Filter $filter, 
        \Magento\Backend\Model\Auth\Session $authSession, 
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory, 
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime, 
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory, 
        \Magento\Sales\Api\OrderManagementInterface $orderManagement, 
        \Evince\AWBnumber\Model\Aramex $aramexModel, 
        \Evince\AWBnumber\Model\Fetchr $fetchrModel, 
        \Evince\AWBnumber\Model\Saee $saeeModel, 
        ResourceConnection $resourceConnection, 
        \Magento\Sales\Model\Convert\Order $convertOrder, 
        \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier, 
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
            \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader,
            \Magento\Sales\Model\Order\Shipment\Track $tracking
    ) {
        parent::__construct($context, $filter);
        $this->authSession = $authSession;
        $this->collectionFactory = $collectionFactory;
        $this->dateTime = $dateTime;
        $this->fileFactory = $fileFactory;
        $this->orderManagement = $orderManagement;
        $this->aramexModel = $aramexModel;
        $this->fetcrModel = $fetchrModel;
        $this->saeeModel = $saeeModel;
        $this->resourceConnection = $resourceConnection;
        $this->convertOrder = $convertOrder;
        $this->shipmentNotifier = $shipmentNotifier;
        $this->scopeConfig = $scopeConfig;
        $this->shipmentLoader = $shipmentLoader;
        $this->tracking = $tracking;
    }

    protected function massAction(AbstractCollection $collection) {

        $resultRedirect = $this->resultRedirectFactory->create();
        foreach ($collection->getItems() as $order) {
            if (!$order->getEntityId()) {
                continue;
            }

            $storeId = $order->getData('store_id');
            $shippingaddress = $order->getShippingAddress();
            $shippingCity = $shippingaddress->getData('city');

            switch ($storeId) {
                case "1":
                    $getShippingMethod = $this->getShippingMethod($shippingCity);
                    break;
                case "2":
                    $getShippingMethod = $this->getArabicShippingMethod($shippingCity);
                    break;
            }
            //echo $getShippingMethod; exit;
            switch (strtolower($getShippingMethod)) {
                case "aramex":
                    $_aramex_response = $this->aramexModel->aramexAwbNumber($order);
                    if ($_aramex_response['type'] == 'error') {
                        $this->messageManager->addError(__($_aramex_response['message']));
                    } else {
                        $customParam = 'Aramex';
                        $this->createShipmentManually($order,$customParam);
                        $this->messageManager->addSuccess(__($_aramex_response['message']));
                    }
                    break;
                case "fetchr":
                    $this->fetcrModel->CreateDropshipOrders($order);
                    break;
                case "saee":
                    $this->saeeModel->saeeAwbNumber($order);
                    $customParam = 'Saee';
                    $this->createShipmentManually($order,$customParam);
                    break;
            }
        }

        return $resultRedirect->setPath('sales/order/index');
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

    public function createShipmentManually($order,$customParams) {

        // Load the order increment ID

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $getOrder = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementID($order->getData('increment_id'));


        // Check if order can be shipped or has already shipped
        if (!$getOrder->canShip()) {
            throw new \Magento\Framework\Exception\LocalizedException(
            __('You can\'t create an shipment.')
            );
        }

        // Initialize the order shipment object
        $convertOrder = $objectManager->create('Magento\Sales\Model\Convert\Order');
        $shipment = $convertOrder->toShipment($getOrder);

        // Loop through order items
        foreach ($getOrder->getAllItems() AS $orderItem) {
            // Check if order item has qty to ship or is virtual
            if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }

            $qtyShipped = $orderItem->getQtyToShip();

            // Create shipment item with qty
            $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);

            // Add shipment item to shipment
            $shipment->addItem($shipmentItem);
        }

        // Register shipment
        $shipment->register();

        //$shipment->getOrder()->setIsInProcess(true);
        $getShipmentStatus = $this->scopeConfig->getValue('awbshipment/order/status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $getOrder->setState($getShipmentStatus)->setStatus($getShipmentStatus);
        $getOrder->save();

        try {
            // Save created shipment and order
            $shipment->save();
            $shipment->getOrder()->save();

//            // update tracking number
                $this->shipmentLoader->setOrderId($getOrder->getId());
                $this->shipmentLoader->setTracking(null);
                if($customParams == 'Aramex')
                {
                    $data = array(
                        'carrier_code' => 'aramex',
                        'title' => 'Aramex',
                        'number' => $getOrder->getData('aramex_waybill_number'), // Replace with your tracking number
                    );
                }
                if($customParams == 'Saee')
                {
                    $data = array(
                        'carrier_code' => 'custom',
                        'title' => 'Saee',
                        'number' => $getOrder->getData('aramex_waybill_number'), // Replace with your tracking number
                    );
                    
                }
                $this->shipmentLoader->setShipment($data);
                
                $track = $objectManager->create('Magento\Sales\Model\Order\Shipment\TrackFactory')->create()->addData($data);
                $shipment->addTrack($track)->save();
            
            // Send email
            $objectManager->create('Magento\Shipping\Model\ShipmentNotifier')
                    ->notify($shipment);

            $shipment->save();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
            __($e->getMessage())
            );
        }
    }

}