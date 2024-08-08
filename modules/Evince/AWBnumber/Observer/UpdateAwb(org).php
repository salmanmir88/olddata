<?php
namespace Evince\AWBnumber\Observer;

use Magento\Framework\Event\ObserverInterface;

class UpdateAwb implements ObserverInterface
{
    protected $resourceConnection;
    protected $aramexShipModel;
    protected $fetcrModel;
    protected $saeeShipModel;
    protected $redirect;
    protected $shipmentSender;
    protected $messageManager;
    
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Evince\AWBnumber\Model\AwbShip\AramexShip $aramexShipModel,
        \Evince\AWBnumber\Model\Fetchr $fetchrModel, 
        \Evince\AWBnumber\Model\AwbShip\SaeeShip $saeeShipModel,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->resourceConnection = $resourceConnection;
        $this->aramexShipModel = $aramexShipModel;
        $this->fetcrModel = $fetchrModel;
        $this->saeeShipModel = $saeeShipModel;
        $this->redirect = $redirect;
        $this->shipmentSender = $shipmentSender;
        $this->messageManager = $messageManager;
    }
    
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        
        //update AWB
        
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
            switch ($getShippingMethod) {
                case "aramex":
                    $_aramex_response = $this->aramexShipModel->createAramexShipment($order,$shipment);
                    if($_aramex_response['type'] == 'error')
                    {
                        $this->messageManager->addError(__($_aramex_response['message']));
                    }
                    else
                    {
                        $this->messageManager->addSuccess(__($_aramex_response['message']));
                    }
                    break;
                case "fetchr":
                    $this->fetcrModel->CreateDropshipOrders($order);
                    break;
                case "saee":
                    $this->saeeShipModel->createSaeeShipment($order,$shipment);
                    break;
            }
            
//            $controller = $observer->getControllerAction();
//        $this->redirect->redirect($controller->getResponse(), 'customer/account/login');
        //$this->shipmentSender->send($shipment);
        return $this;
    }
    
    public function getShippingMethod($city) {
        
        $connection = $this->resourceConnection->getConnection();
        $query = 'SELECT `courier` FROM `courier_manager` WHERE `city` = "' . $city . '"';
        $shipping_method = $connection->fetchOne($query);
        return $shipping_method;
    }
    public function getArabicShippingMethod($city)
    {
        $_ar_connection = $this->resourceConnection->getConnection();
        $_ar_query = 'SELECT `courier` FROM `courier_manager` WHERE `city_arabic` = "' . $city . '"';
        $_ar_shipping_method = $_ar_connection->fetchOne($_ar_query);
        return $_ar_shipping_method;
        
    }
}