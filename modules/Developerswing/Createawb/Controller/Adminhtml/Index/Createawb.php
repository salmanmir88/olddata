<?php

namespace Developerswing\Createawb\Controller\Adminhtml\Index;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\Filesystem\DirectoryList;

class Createawb extends \Magento\Backend\App\Action {

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
    protected $orderRepository;
    protected $request;
    protected $armexShip;
    protected $saeeShip;

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
        \Magento\Sales\Model\Order\Shipment\Track $tracking,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\App\Request\Http $request,
        \Evince\AWBnumber\Model\AwbShip\AramexShip $armexShip,
        \Evince\AWBnumber\Model\AwbShip\SaeeShip $saeeShip
    ) {
        parent::__construct($context);
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
        $this->orderRepository = $orderRepository;
        $this->request = $request;
        $this->armexShip = $armexShip;
        $this->saeeShip = $saeeShip;
    }

    public function execute() {
        $orderId = $this->request->getParam('order_id');
        $order = $this->orderRepository->get($orderId);
        $resultRedirect = $this->resultRedirectFactory->create();
            if (!$order->getEntityId()) {
                return;
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
            
            switch ($getShippingMethod) {
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
                    //$this->fetcrModel->CreateDropshipOrders($order);
                    break;
                case "saee":
                    $this->saeeModel->saeeAwbNumber($order);
                    $customParam = 'Saee';
                    $this->createSaeeShipment($order,$customParam);
                    break;
            }

        return $resultRedirect->setPath('sales/order/view',['order_id'=>$orderId]);
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
        $shipmentcollection = $order->getShipmentsCollection();
        foreach($shipmentcollection as $shipments)
        {
           $shipment = $shipments;   
        }

        try {
             $this->armexShip->createAramexShipment($order,$shipment);
        } catch (\Exception $e) { 
            throw new \Magento\Framework\Exception\LocalizedException(
            __($e->getMessage())
            );
        }
    }
    public function createSaeeShipment($order,$customParams) {
        $shipmentcollection = $order->getShipmentsCollection();
        foreach($shipmentcollection as $shipments)
        {
           $shipment = $shipments;   
        }

        try {
             $this->saeeShip->createSaeeShipment($order,$shipment);
        } catch (\Exception $e) { 
            throw new \Magento\Framework\Exception\LocalizedException(
            __($e->getMessage())
            );
        }
    }

}