<?php
namespace Evince\AWBnumber\Model\AwbShip;

class SaeeShip extends \Magento\Framework\Model\AbstractModel {

    CONST CREATE_ORDER_NEW = '/deliveryrequest/new';

    protected $messageManager;
    protected $resourceConnection;
    protected $saeeHelper;
    protected $scopeConfig;
    protected $orderRepository;
    protected $awbHelper;

    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Saee\ShipmentMethod\Helper\SaeeUtils $saeeHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Evince\AWBnumber\Helper\Data $awbHelper
    ) {
        $this->messageManager = $messageManager;
        $this->resourceConnection = $resourceConnection;
        $this->saeeHelper = $saeeHelper;
        $this->scopeConfig = $scopeConfig;
        $this->orderRepository = $orderRepository;
        $this->awbHelper = $awbHelper;
    }

    public function createSaeeShipment($order,$shipment) {

        try {

            if($order->getData('aramex_waybill_number') == "")
            {
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

                $sql          = "Select * FROM  amasty_amcheckout_order_custom_fields WHERE name = 'custom_field_2' AND order_id = ".$order->getId();
                $singleRow     = $this->resourceConnection->getConnection()->fetchRow($sql);
                $sql2          = "Select * FROM  amasty_amcheckout_order_custom_fields WHERE name = 'custom_field_1' AND order_id = ".$order->getId();
                $singleRow2    = $this->resourceConnection->getConnection()->fetchRow($sql2);

                $secondphone   = isset($singleRow['billing_value'])?$singleRow['billing_value']:'';
                if(!$secondphone)
                {
                $secondphone   = isset($singleRow['shipping_value'])?$singleRow['shipping_value']:'';    
                }

                $neighnourhood = isset($singleRow2['billing_value'])?$singleRow2['billing_value']:'';
                if(!$neighnourhood)
                {
                $neighnourhood = isset($singleRow2['shipping_value'])?$singleRow2['shipping_value']:'';    
                }
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
                        "mobile2"=>$secondphone,
                        "streetaddress" => $neighnourhood." ".$address['street'],
                        "city" => $this->awbHelper->getCityName($address['city'],1),
                        "district" => "",
                        "streetaddress2" => "",
                        "state" => "",
                        "hs_code"=>"",
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
                        "mobile2"=>$secondphone,
                        "streetaddress" => $neighnourhood." ".$address['street'],
                        "city" => $this->awbHelper->getCityName($address['city'],2),
                        "district" => "",
                        "streetaddress2" => "",
                        "state" => "",
                        "hs_code" => "",
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

                    //Save AWB number 
                    $data = array(
                        'carrier_code' => 'custom',
                        'title' => 'Saee',
                        'number' => $wayBillNumber, // Replace with your tracking number
                    );

                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $track = $objectManager->create('Magento\Sales\Model\Order\Shipment\TrackFactory')->create()->addData($data);
                    $shipment->addTrack($track)->save();

                    // generate awb link
                    $downloadlink = 'https://corporate.saeex.com/deliveryrequest/printsticker/pdf/'.$wayBillNumber;
                    $saee = strtolower('Saee');
                    $sql = "UPDATE `sales_order_grid` SET `awb_link` = '".$downloadlink."' , `courier` = '".$saee."' WHERE `increment_id` = ".$order->getIncrementId()." ";
                    $this->resourceConnection->getConnection()->query($sql);
                    $message =  $this->messageManager->addSuccess(__($response['message']));

                    //update order status

                    $getShipmentStatus = $this->scopeConfig->getValue('awbshipment/order/status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    $_order = $this->orderRepository->get($order->getId());
                    $_order->setState($getShipmentStatus);
                    $_order->setStatus($getShipmentStatus);

                    try {
                        $this->orderRepository->save($_order);
                    } catch (\Exception $e) {
                        $this->logger->error($e);
                        $this->messageManager->addExceptionMessage($e, $e->getMessage());
                    }
//                    $objectManager1 = \Magento\Framework\App\ObjectManager::getInstance();
//                    $order = $objectManager1->create('\Magento\Sales\Model\Order')->load($order->getId()); 
//                    $order->setState($getShipmentStatus)->setStatus($getShipmentStatus);
//                    $order->save();
                }
                else
                {
                    $message =  $this->messageManager->addError(__($response['error']));
                }

                return $message;
            }
            
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
}
