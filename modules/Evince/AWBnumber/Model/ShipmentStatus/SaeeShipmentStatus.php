<?php
namespace Evince\AWBnumber\Model\ShipmentStatus;


class SaeeShipmentStatus extends \Magento\Framework\Model\AbstractModel {

    protected $resourceConnection;
    protected $orderRepository;
    protected $json;
    
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\Serialize\Serializer\Json $json
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->orderRepository = $orderRepository;
        $this->json = $json;
    }

    public function checkSaeeShipmentStatus($order) {
        
        
            // get AWB number
            $connection = $this->resourceConnection->getConnection();
            $query = "SELECT `track_number` FROM `sales_shipment_track` WHERE `title` = 'Saee'  AND `order_id`= ". $order->getId();
            //$query = "SELECT `track_number` FROM `sales_shipment_track` WHERE `title` = 'Saee' AND `order_id`= 31832";
            //echo $query; exit;
            $awb_number = $connection->fetchOne($query);
        
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => 'https://corporate.saeex.com/tracking?trackingnum='.$awb_number,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'GET',
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            $jsonDecode = $this->json->unserialize($response);
            
            if($jsonDecode['success'] == 1)
            {
                if(is_array($jsonDecode['details']))
                {
                    foreach($jsonDecode['details'] as $details)
                    {
                        if($details['status'] == 5)
                        {
                            $getShipmentStatus = $this->scopeConfig->getValue('awbshipment/order/deliverd', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                            $_order = $this->orderRepository->get($order->getId());
                            $_order->setState($getShipmentStatus);
                            $_order->setStatus($getShipmentStatus);

                            try {
                                $this->orderRepository->save($_order);
                            } catch (\Exception $e) {
                                $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/saee_shipment_error.log');
                                $logger = new \Zend\Log\Logger();
                                $logger->addWriter($writer);
                                $logger->info($e->getMessage());

                            }

                        }
                    }
                }

            }
            else
            {
                $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/saee_traking_error.log');
                $logger = new \Zend\Log\Logger();
                $logger->addWriter($writer);
                $logger->info($jsonDecode['error']);
                
            }
    }

}
