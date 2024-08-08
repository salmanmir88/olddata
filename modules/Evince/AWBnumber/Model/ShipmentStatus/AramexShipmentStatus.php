<?php
namespace Evince\AWBnumber\Model\ShipmentStatus;


class AramexShipmentStatus extends \Magento\Framework\Model\AbstractModel {

    protected $reader;
    protected $soapClientFactory;
    protected $scopeConfig;
    protected $resourceConnection;
    protected $orderRepository;

    public function __construct(
        \Magento\Framework\Module\Dir\Reader $reader, 
        \Magento\Framework\Webapi\Soap\ClientFactory $soapClientFactory, 
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->reader = $reader;
        $this->soapClientFactory = $soapClientFactory;
        $this->scopeConfig = $scopeConfig;
        $this->resourceConnection = $resourceConnection;
        $this->orderRepository = $orderRepository;
        
    }

    public function checkAramexShipmentStatus($order) {
        
        $baseUrl = $this->reader->getModuleDir('etc', 'Evince_AWBnumber') . '/wsdl/Aramex/';
        $soapClient = $this->soapClientFactory->create($baseUrl .
                'Tracking.wsdl', ['version' => SOAP_1_1, 'trace' => 1, 'keep_alive' => false]);
        
        // get AWB number
        $connection = $this->resourceConnection->getConnection();
        $query = "SELECT `track_number` FROM `sales_shipment_track` WHERE `carrier_code` = 'aramex'  AND `order_id`= ". $order->getId();
        $awb_number = $connection->fetchOne($query);
        
        
        $params = array(
            'ClientInfo' => array(
                'AccountCountryCode' => $this->scopeConfig->getValue('aramex/settings/account_country_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'AccountEntity' => $this->scopeConfig->getValue('aramex/settings/account_entity', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'AccountNumber' => $this->scopeConfig->getValue('aramex/settings/account_number', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'AccountPin' => $this->scopeConfig->getValue('aramex/settings/account_pin', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'UserName' => $this->scopeConfig->getValue('aramex/settings/user_name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'Password' => $this->scopeConfig->getValue('aramex/settings/password', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'Version' => 'v1.0'
            ),
            'Transaction' => array(
                'Reference1' => $order->getIncrementId()
            ),
            'Shipments' => array(
                $awb_number
            )
        );
        
        try {
            $auth_call = $soapClient->TrackShipments($params);
            
            if(!empty($auth_call->TrackingResults->KeyValueOfstringArrayOfTrackingResultmFAkxlpY->Value->TrackingResult))
            {
                if(is_array($auth_call->TrackingResults->KeyValueOfstringArrayOfTrackingResultmFAkxlpY->Value->TrackingResult))
                {
                    
                    $transactionResults = $auth_call->TrackingResults->KeyValueOfstringArrayOfTrackingResultmFAkxlpY->Value->TrackingResult;
                    
                    foreach ($transactionResults as $transaction)
                    {
                        if($transaction->UpdateDescription == "Delivered")
                        {
                            $getShipmentStatus = $this->scopeConfig->getValue('awbshipment/order/deliverd', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                            $_order = $this->orderRepository->get($order->getId());
                            $_order->setState($getShipmentStatus);
                            $_order->setStatus($getShipmentStatus);

                            try {
                                $this->orderRepository->save($_order);
                            } catch (\Exception $e) {
                                $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/update_shipment_error.log');
                                $logger = new \Zend\Log\Logger();
                                $logger->addWriter($writer);
                                $logger->info($e->getMessage());
                                
                            }
                        }
                    }
                }

                
            }
            
        } catch (SoapFault $fault) {
            //die('Error : ' . $fault->faultstring);
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/fault_soap.log');
                                $logger = new \Zend\Log\Logger();
                                $logger->addWriter($writer);
                                $logger->info($fault->faultstring);
        }
    }

}
