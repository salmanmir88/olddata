<?php
namespace Evince\AWBnumber\Model\AwbShip;


class AramexShip extends \Magento\Framework\Model\AbstractModel {
    
    protected $reader;
    protected $soapClientFactory;
    protected $scopeConfig;
    protected $shipmentLoader;
    protected $order;
    protected $tracking;
    protected $helper;
    protected $countryFactory;
    protected $messageManager;
    protected $resourceConnection;
    protected $orderRepository;
    protected $awbHelper;

    public function __construct(
        \Magento\Framework\Module\Dir\Reader $reader, 
        \Magento\Framework\Webapi\Soap\ClientFactory $soapClientFactory, 
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Shipping\Controller\Adminhtml\Order\ShipmentLoader $shipmentLoader,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\Order\Shipment\Track $tracking,
        \Aramex\Shipping\Helper\Data $helper,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Evince\AWBnumber\Helper\Data $awbHelper
    )
    {
        $this->reader = $reader;
        $this->soapClientFactory = $soapClientFactory;
        $this->scopeConfig = $scopeConfig;
        $this->shipmentLoader = $shipmentLoader;
        $this->order = $order;
        $this->tracking = $tracking;
        $this->helper = $helper;
        $this->countryFactory = $countryFactory;
        $this->messageManager = $messageManager;
        $this->resourceConnection = $resourceConnection;
        $this->orderRepository = $orderRepository;
        $this->awbHelper = $awbHelper;
        
    }
    
    public function createAramexShipment($order,$shipment) {
        
        $post=[];
        $descriptionOfGoods = "";
        if($order->getData('aramex_waybill_number') == "")
        {
            $order = $this->order->load($order->getId());
            foreach ($order->getAllVisibleItems() as $itemname) {
                $descriptionOfGoods .= $itemname->getId() . ' - ' . trim($itemname->getName());
            }
            $descriptionOfGoods = substr($descriptionOfGoods, 0, 65);
            $major_par = $this->getParams($order,$post, $descriptionOfGoods);
        
            $aramex_errors = $this->makeShipment($major_par, $order,$shipment, $post);
        }
    }
    
    public function getParams($order, $post, $descriptionOfGoods)
    {
        //$totalItems = (trim($post['number_pieces']) == '') ? 1 : (int) $post['number_pieces'];
        $totalItems = 0;
        $orderItems = $order->getAllItems();
        foreach ($orderItems as $item)
        {
                $totalItems = $totalItems + $item->getQtyOrdered();
        }
        
        //attachment
        $totalWeight = $order->getWeight();
        $params = [];
            //shipper parameters
            $params['Shipper'] = [
                'Reference1' => $order->getIncrementId(),
                'Reference2' => '',
                'AccountNumber' => $this->scopeConfig->getValue('aramex/settings/account_number', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                //Party Address
                'PartyAddress' => [
                    'Line1' => $this->scopeConfig->getValue('aramex/shipperdetail/address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                    'Line2' => '',
                    'Line3' => '',
                    'City' => $this->scopeConfig->getValue('aramex/shipperdetail/city', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                    'StateOrProvinceCode' => $this->scopeConfig->getValue('aramex/shipperdetail/state', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                    'PostCode' => $this->scopeConfig->getValue('aramex/shipperdetail/postalcode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                    'CountryCode' => $this->scopeConfig->getValue('aramex/settings/account_country_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                ],
                //Contact Info
                'Contact' => [
                    'Department' => '',
                    'PersonName' => $this->scopeConfig->getValue('aramex/shipperdetail/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                    'Title' => '',
                    'CompanyName' => $this->scopeConfig->getValue('aramex/shipperdetail/company', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                    'PhoneNumber1' => $this->scopeConfig->getValue('aramex/shipperdetail/phone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                    'PhoneNumber1Ext' => '',
                    'PhoneNumber2' => '',
                    'PhoneNumber2Ext' => '',
                    'FaxNumber' => '',
                    'CellPhone' =>$this->scopeConfig->getValue('aramex/shipperdetail/phone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) ,
                    'EmailAddress' => '',
                    'Type' => ''
                ],
            ];
            //consinee parameters
            
            $shipping = $order->getShippingAddress();
            
            $billing = $order->getBillingAddress();
            $post['aramex_shipment_receiver_name'] = ($shipping) ? $shipping->getName() : '';
            $company_name = isset($billing) ? $billing->getData('company') : '';
            $company_name = ($company_name) ? $company_name : '';
            $company_name = (empty($company_name) and $shipping) ? $shipping->getName() : $company_name;
            $company_name = ($shipping) ? $shipping->getData('company') : '';
            $orderStoreId = $order->getData('store_id');
            $cellPhone    = ($shipping) ? $shipping->getData('telephone') : '';
            $sql       = "Select * FROM  amasty_amcheckout_order_custom_fields WHERE name = 'custom_field_2' AND order_id = ".$order->getId();
            $singleRow = $this->resourceConnection->getConnection()->fetchRow($sql);
            $sql2       = "Select * FROM  amasty_amcheckout_order_custom_fields WHERE name = 'custom_field_1' AND order_id = ".$order->getId();
            $singleRow2 = $this->resourceConnection->getConnection()->fetchRow($sql2);
            $post['aramex_shipment_receiver_secondphone'] = isset($singleRow['billing_value'])?$singleRow['billing_value']:'';
            $post['aramex_shipment_receiver_neighnourhood'] = isset($singleRow2['billing_value'])?$singleRow2['billing_value']:'';
            if(!$post['aramex_shipment_receiver_secondphone'])
            {
              $cellPhone = ($shipping) ? $shipping->getData('telephone') : '';
            }
            
            if($orderStoreId == "1")
            {
                //Eng store
                $params['Consignee'] = [
                    'Reference1' => $order->getIncrementId(),
                    'Reference2' => '',
                    'AccountNumber' => '',
                    //Party Address
                    'PartyAddress' => [
                        'Line1' => ($shipping) ? $shipping->getData('street') : '',
                        'Line2' => $post['aramex_shipment_receiver_neighnourhood'],
                        'Line3' => '',
                        'City' => ($shipping) ? $this->awbHelper->getCityName($shipping->getData('city'),1) : '',
                        'StateOrProvinceCode' => '',
                        'PostCode' => ($shipping) ? $shipping->getData('postcode') : '',
                        'CountryCode' => ($shipping) ? $shipping->getData('country_id') : '',
                    ],
                    //Contact Info
                    'Contact' => [
                        'Department' => '',
                        'PersonName' => ($shipping) ? $shipping->getName() : '',
                        'Title' => '',
                        'CompanyName' => (!empty($company_name)) ? $company_name : $post['aramex_shipment_receiver_name'],
                        'PhoneNumber1' => ($shipping) ? $shipping->getData('telephone') : '',
                        'PhoneNumber1Ext' => '',
                        'PhoneNumber2' => "",
                        'PhoneNumber2Ext' => '',
                        'FaxNumber' => '',
                        'CellPhone' => $cellPhone,
                        'EmailAddress' => $order->getData('customer_email'),
                        'Type' => ''
                    ]
                ];
            }
            else
            {
                $city =  $shipping->getData('city');
                $translateCity  = $this->awbHelper->getCityName($city,2);
                //Arabic store
                $params['Consignee'] = [
                    'Reference1' => $order->getIncrementId(),
                    'Reference2' => '',
                    'AccountNumber' => '',
                    //Party Address
                    'PartyAddress' => [
                        'Line1' => ($shipping) ? $shipping->getData('street') : '',
                        'Line2' => $post['aramex_shipment_receiver_neighnourhood'],
                        'Line3' => '',
                        'City' =>$translateCity ,
                        'StateOrProvinceCode' => '',
                        'PostCode' => ($shipping) ? $shipping->getData('postcode') : '',
                        'CountryCode' => ($shipping) ? $shipping->getData('country_id') : '',
                    ],
                    //Contact Info
                    'Contact' => [
                        'Department' => '',
                        'PersonName' => ($shipping) ? $shipping->getName() : '',
                        'Title' => '',
                        'CompanyName' => (!empty($company_name)) ? $company_name : $post['aramex_shipment_receiver_name'],
                        'PhoneNumber1' => ($shipping) ? $shipping->getData('telephone') : '',
                        'PhoneNumber1Ext' => '',
                        'PhoneNumber2' => '',
                        'PhoneNumber2Ext' => '',
                        'FaxNumber' => '',
                        'CellPhone' => $cellPhone,
                        'EmailAddress' => $order->getData('customer_email'),
                        'Type' => ''
                    ]
                ];
                
            }
            
            $countryCode = $shipping->getData('country_id');
            $country = $this->countryFactory->create()->loadByCode($countryCode);
            $shipToCountry = $country->getName();
            if($shipToCountry == "Saudi Arabia")
            {
                $post['aramex_shipment_info_product_group'] = 'DOM';
                $post['aramex_shipment_info_product_type'] = 'ONP';
                $post['aramex_shipment_currency_code'] = 'SAR';
            }
            else 
            {
                $post['aramex_shipment_info_product_group'] = 'EXP';
                $post['aramex_shipment_info_product_type'] = 'PDX';
                $post['aramex_shipment_currency_code'] = 'USD';
            }
            
            // EVINCE CHECK COD OR PREPAID
            $isOffline = $order->getPayment()->getMethodInstance()->isOffline();
            $service = '';
            $cashOnDelivery = '';
            if ($isOffline) {
                // OFFLINE PAYMENT METHOD
                $service = "CODS";
                $cashOnDelivery = $order->getData('grand_total');
            } 
            else 
            {
                // ONLINE PAYMENT METHOD
                $service='';
                $cashOnDelivery = 0;
            }
            
            
            // Other Main Shipment Parameters
            $aramex_shipment_description = $this->getShipmentDescription($order);
            $params['Reference1'] =  $order->getIncrementId();
            $params['Reference2'] = '';
            $params['Reference3'] = '';
            $params['ForeignHAWB'] = '';
            $params['TransportType'] = 0;
            $params['ShippingDateTime'] = time();
            $params['DueDate'] = time() + (7 * 24 * 60 * 60);
            $params['PickupLocation'] = 'Reception';
            $params['PickupGUID'] = '';
            $params['Comments'] = '';
            $params['AccountingInstrcutions'] = '';
            $params['OperationsInstructions'] = '';
            $params['Details'] = [
                'Dimensions' => [
                    'Length' => '0',
                    'Width' => '0',
                    'Height' => '0',
                    'Unit' => 'cm'
                ],
                'ActualWeight' => [
                    'Value' => $totalWeight,
                    'Unit' => 'KG'
                ],
                'ProductGroup' => $post['aramex_shipment_info_product_group'],
                'ProductType' => $post['aramex_shipment_info_product_type'],
                'PaymentType' => 'P',
                'PaymentOptions' => '',
                'Services' => $service,
                'NumberOfPieces' => $totalItems,
                'DescriptionOfGoods' => $aramex_shipment_description,
                'GoodsOriginCountry' => $this->scopeConfig->getValue('aramex/settings/account_country_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE),
                'Items' => $totalItems,
            ];
            
            $params['Details']['CashOnDeliveryAmount'] = [
                'Value' => $cashOnDelivery,
                'CurrencyCode' =>  $post['aramex_shipment_currency_code']
            ];

            $major_par['Shipments'][] = $params;

            $clientInfo = $this->helper->getClientInfo();

            $major_par['ClientInfo'] = $clientInfo;
            $report_id = (int) $this->scopeConfig->getValue(
                'aramex/config/report_id',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        if (!$report_id) {
            $report_id = 9729;
        }
        $major_par['LabelInfo'] = [
                'ReportID' => $report_id,
                'ReportType' => 'URL'
            ];
        //$this->_session->setData("form_data", $post);
        return $major_par;
    }
    
    public function makeShipment($major_par, $order,$shipment, $post)
    {
        $baseUrl = $this->reader->getModuleDir('etc', 'Evince_AWBnumber') . '/wsdl/Aramex/';

        $soapClient = $this->soapClientFactory->create($baseUrl .
                'shipping-services-api-wsdl.wsdl', ['version' => SOAP_1_1, 'trace' => 1, 'keep_alive' => false]);
        try {
            $items = $order->getAllVisibleItems();
            //create shipment call
                $auth_call = $soapClient->CreateShipments($major_par);
            if ($auth_call->HasErrors) {
                $this->processError($auth_call);
                 return ['aramex_errors' => true];
            } else {
                
                // update tracking number
                $data = array(
                    'carrier_code' => 'aramex',
                    'title' => 'Aramex',
                    'number' => $auth_call->Shipments->ProcessedShipment->ID, // Replace with your tracking number
                );
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $track = $objectManager->create('Magento\Sales\Model\Order\Shipment\TrackFactory')->create()->addData($data);
                $shipment->addTrack($track)->save();

                // generate AWB link
                $url = $auth_call->Shipments->ProcessedShipment->ShipmentLabel->LabelURL;
                $sql = "UPDATE `sales_order_grid` SET `awb_link` = '".$url."' , `courier` = 'aramex' WHERE `increment_id` = ".$order->getIncrementId()." ";
                $this->resourceConnection->getConnection()->query($sql);
                
                //update order status
                $getShipmentStatus = $this->scopeConfig->getValue('awbshipment/order/status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);                
                
                $_order = $this->orderRepository->get($order->getId());
                $_order->setState($getShipmentStatus);
                $_order->setStatus($getShipmentStatus);

                try {
                    $this->orderRepository->save($_order);
                } catch (\Exception $e) {
                    //$this->logger->error($e);
                    $this->messageManager->addExceptionMessage($e, $e->getMessage());
                }
//                $getShipmentStatus = $this->scopeConfig->getValue('awbshipment/order/status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
//                $objectManager1 = \Magento\Framework\App\ObjectManager::getInstance();
//                $order = $objectManager1->create('\Magento\Sales\Model\Order')->load($order->getId()); 
//                $order->setState($getShipmentStatus)->setStatus($getShipmentStatus);
//                $order->save();
                

            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            //$this->messageManager->addError('adminhtml/session')->addError($e->getMessage());
            return ['aramex_errors' => true];
        }
    }
    
    public function processError($auth_call)
    {
        if (empty($auth_call->Shipments)) {
            if (!is_object($auth_call->Notifications->Notification)) {
                foreach ($auth_call->Notifications->Notification as $notify_error) {
                    $this->messageManager->addError('Aramex: ' . $notify_error->Code . ' - ' .
                            $notify_error->Message);
                }
            } else {
                $this->messageManager->addError('Aramex: ' . $auth_call->Notifications->Notification->Code
                   . ' - ' . $auth_call->Notifications->Notification->Message);
            }
        } elseif (isset($auth_call->Notifications->Notification)) {
            $this->messageManager->addError('Aramex: ' . $auth_call->Notifications->Notification->Code
            . ' - ' . $auth_call->Notifications->Notification->Message);
        } else {
            if (!is_object($auth_call->Shipments->ProcessedShipment->Notifications->Notification)) {
                $notification_string = '';
                foreach ($auth_call->Shipments->ProcessedShipment->Notifications->Notification as $notification_error) {
                            $notification_string .= $notification_error->Code . ' - '
                                    . $notification_error->Message . ' <br />';
                }
                $this->messageManager->addError($notification_string);
            } else {
                $this->messageManager->addError('Aramex: ' . $auth_call->Shipments->ProcessedShipment->
                        Notifications->Notification->Code . ' - ' . $auth_call->Shipments->ProcessedShipment->
                        Notifications->Notification->Message);
            }
        }
    }
    
    public function getShipmentDescription($order) {
        $aramex_shipment_description = '';
        foreach ($order->getAllVisibleItems() as $itemname) {
            if ($itemname->getQtyOrdered() > $itemname->getQtyShipped()) {
                $aramex_shipment_description = $aramex_shipment_description . $itemname->getId() . ' - ' .
                        trim($itemname->getName());
            }
        }
        return $aramex_shipment_description;
    }
    
    public function getCityNameInEng($arcity)
    {
        $connection = $this->resourceConnection->getConnection();
        $query = "SELECT `city` FROM `courier_manager` WHERE `city_arabic` LIKE '%".$arcity."%' ";
        $shipping_method = $connection->fetchOne($query);
        return $shipping_method;
        
    }
}