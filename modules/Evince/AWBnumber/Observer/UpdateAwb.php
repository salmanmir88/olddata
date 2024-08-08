<?php
namespace Evince\AWBnumber\Observer;

use Magento\Framework\Event\ObserverInterface;

class UpdateAwb implements ObserverInterface
{
    protected $resourceConnection;
    protected $aramexShipModel;
    protected $fetcrModel;
    protected $saeeShipModel;
    protected $logistiqShipModel;
    protected $redirect;
    protected $shipmentSender;
    protected $messageManager;
    protected $helper;
    
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Evince\AWBnumber\Model\AwbShip\AramexShip $aramexShipModel,
        \Evince\AWBnumber\Model\Fetchr $fetchrModel, 
        \Evince\AWBnumber\Model\AwbShip\SaeeShip $saeeShipModel,
        \Evince\AWBnumber\Model\AwbShip\Logistiq $logistiqShipModel,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Sales\Model\Order\Email\Sender\ShipmentSender $shipmentSender,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Evince\AWBnumber\Helper\Data $helper
    )
    {
        $this->resourceConnection = $resourceConnection;
        $this->aramexShipModel = $aramexShipModel;
        $this->fetcrModel = $fetchrModel;
        $this->saeeShipModel = $saeeShipModel;
        $this->logistiqShipModel = $logistiqShipModel;
        $this->redirect = $redirect;
        $this->shipmentSender = $shipmentSender;
        $this->messageManager = $messageManager;
        $this->helper = $helper;
    }
    
    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        
        $payment = $order->getPayment();
        $paymentMethod = $payment->getMethod();

        //update AWB
        
        $storeId = $order->getData('store_id');
        $shippingaddress = $order->getShippingAddress();
        $shippingCity = $shippingaddress->getData('city');

        /*switch ($storeId) {
                case "1":
                    $getShippingMethod = $this->getShippingMethod($shippingCity);
                    break;
                case "2":
                    $getShippingMethod = $this->getArabicShippingMethod($shippingCity);
                    break;
            }*/

            switch ($paymentMethod){
                case "myfatoorah_gateway":
                $this->logistiqShipModel->createLogistiqShipment($order,$shipment);
                break;
                case "myfatoorah_gatewaydummy":
                $this->logistiqShipModel->createLogistiqShipment($order,$shipment);
                break;
                case "cashondelivery":
                $this->saeeShipModel->createSaeeShipment($order,$shipment);
                break;

            }
            /*switch ($getShippingMethod) {
                case "saee":
                    if($paymentMethod=='myfatoorah_gateway'){
                      $this->logistiqShipModel->createLogistiqShipment($order,$shipment);
                    }else{
                      $this->saeeShipModel->createSaeeShipment($order,$shipment);
                    }
                    break;
                case "logistiq":
                    if($paymentMethod=='cashondelivery'){
                      $this->logistiqShipModel->createLogistiqShipment($order,$shipment);
                    }else{
                      $this->saeeShipModel->createSaeeShipment($order,$shipment);
                    }
                    break;
            }*/
            
//            $controller = $observer->getControllerAction();
//        $this->redirect->redirect($controller->getResponse(), 'customer/account/login');
        //$this->shipmentSender->send($shipment);
        return $this;
    }
    
    public function getShippingMethod($city) {
        
        $connection = $this->resourceConnection->getConnection();
        $query = 'SELECT `courier` FROM `courier_manager` WHERE `city` LIKE "%' . $city . '%"';
        $shipping_method = $connection->fetchOne($query);
        return $shipping_method;
    }
    public function getArabicShippingMethod($city)
    {
        $_ar_connection = $this->resourceConnection->getConnection();
        $_ar_query = 'SELECT `courier` FROM `courier_manager` WHERE `city_arabic` LIKE "%' . $city . '%"';
        $_ar_shipping_method = $_ar_connection->fetchOne($_ar_query);
        return $_ar_shipping_method;
        
    }
}