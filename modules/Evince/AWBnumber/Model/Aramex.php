<?php
namespace Evince\AWBnumber\Model;


class Aramex extends \Magento\Framework\Model\AbstractModel {

    protected $reader;
    protected $soapClientFactory;
    protected $countryFactory;
    protected $scopeConfig;
    protected $order;
    protected $helper;
    protected $fileFactory;
    protected $resourceConnection;
    
    protected $directory;
    protected $file;

    public function __construct(
        \Magento\Framework\Module\Dir\Reader $reader, 
        \Magento\Framework\Webapi\Soap\ClientFactory $soapClientFactory, 
        \Magento\Directory\Model\CountryFactory $countryFactory, 
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
        \Magento\Sales\Model\Order $order, 
        \Evince\AWBnumber\Helper\Data $helper,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\Filesystem\DirectoryList $directory,
        \Magento\Framework\Filesystem\Io\File $file
    ) {
        $this->reader = $reader;
        $this->soapClientFactory = $soapClientFactory;
        $this->countryFactory = $countryFactory;
        $this->scopeConfig = $scopeConfig;
        $this->order = $order;
        $this->helper = $helper;
        $this->downloader = $fileFactory;
        $this->resourceConnection = $resourceConnection;
        $this->directory = $directory;
        $this->file = $file;
    }

    public function aramexAwbNumber($order) {
        $post = [];
        $post['aramex_shipment_original_reference'] = (int) $order->getIncrementId();
        
        $isShipped = false;
        $itemsv = $order->getAllVisibleItems();
        $totalWeight = 0;
        foreach ($itemsv as $itemvv) {
            $weight = $this->getTotalWeight($itemvv);

            $totalWeight += $weight;
            if ($itemvv->getQtyOrdered() == $itemvv->getQtyShipped()) {
                $isShipped = true;
            }
            //quontity
            $_qty = abs($itemvv->getQtyOrdered() - $itemvv->getQtyShipped());
            if ($_qty == 0 and $isShipped) {
                $_qty = (int) $itemvv->getQtyShipped();
            }

            $post[$itemvv->getId()] = (string) $_qty;
        }

        $post['aramex_items'] = $this->getTotalItems($itemsv, $isShipped);
        $post['order_weight'] = (string) $totalWeight;
        $post['aramex_shipment_shipper_reference'] = $order->getIncrementId();
        $post['aramex_shipment_info_billing_account'] = 1;
        $post['aramex_shipment_shipper_account'] = $this->scopeConfig->
                getValue(
                'aramex/settings/account_number', \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $post['aramex_shipment_shipper_street'] = $this->scopeConfig->getValue(
                'aramex/shipperdetail/address', \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $post['aramex_shipment_shipper_city'] = $this->scopeConfig->getValue(
                'aramex/shipperdetail/city', \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $post['aramex_shipment_shipper_state'] = $this->scopeConfig->getValue(
                'aramex/shipperdetail/state', \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $post['aramex_shipment_shipper_postal'] = $this->scopeConfig->
                getValue(
                'aramex/shipperdetail/postalcode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $post['aramex_shipment_shipper_name'] = $this->scopeConfig->getValue(
                'aramex/shipperdetail/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $post['aramex_shipment_shipper_company'] = $this->scopeConfig->getValue(
                'aramex/shipperdetail/company', \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $post['aramex_shipment_shipper_phone'] = $this->scopeConfig->getValue(
                'aramex/shipperdetail/phone', \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $post['aramex_shipment_shipper_email'] = $this->scopeConfig->getValue(
                'aramex/shipperdetail/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        //shipper parameters
        $post['aramex_shipment_receiver_reference'] = $order->getIncrementId();
        $shipping = $order->getShippingAddress();
        $post['aramex_shipment_receiver_street'] = ($shipping) ? $shipping->getData('street') : '';
        $post['aramex_shipment_receiver_city'] = ($shipping) ? $shipping->getData('city') : '';
        $post['aramex_shipment_receiver_postal'] = ($shipping) ? $shipping->getData('postcode') : '';
        $post['aramex_shipment_receiver_country'] = ($shipping) ? $shipping->getData('country_id') : '';
        $post['aramex_shipment_receiver_name'] = ($shipping) ? $shipping->getName() : '';

        //Contact Info
        $billing = $order->getBillingAddress();
        $post['aramex_shipment_receiver_name'] = ($shipping) ? $shipping->getName() : '';
        $company_name = isset($billing) ? $billing->getData('company') : '';
        $company_name = ($company_name) ? $company_name : '';
        $company_name = (empty($company_name) and $shipping) ? $shipping->getName() : $company_name;
        $company_name = ($shipping) ? $shipping->getData('company') : '';

        $post['aramex_shipment_receiver_company'] = (!empty($company_name)) ? $company_name : $post['aramex_shipment_receiver_name'];
        $post['aramex_shipment_receiver_phone'] = ($shipping) ? $shipping->getData('telephone') : '';
        $post['aramex_shipment_receiver_email'] = $order->getData('customer_email');
        // Other Main Shipment Parameters
        $post['aramex_shipment_info_reference'] = $order->getIncrementId();
        $post['aramex_shipment_info_foreignhawb'] = '';
        $post['aramex_shipment_info_comment'] = '';
        $post['weight_unit'] = 'KG';
        
        $post['aramex_shipment_currency_code'] = $order->getOrderCurrencyCode();
        $aramex_shipment_description = $this->getShipmentDescription($order);
        $post['aramex_shipment_description'] = $aramex_shipment_description;
        $post['aramex_shipment_info_cod_amount'] = ($order->getPayment()->getMethodInstance()->getCode() != 'creditcard') ? (string) round($order->getData('grand_total'), 2) : '';
        $post['aramex_return_shipment_creation_date'] = "create";
        $post['aramex_shipment_referer'] = 0;
        
        $replay = $this->postAction($order, $order->getShippingMethod(), $post);
        
        //echo "<pre>"; var_dump($replay); echo "</pre>";  exit;
        $response = array();
                
//        if(is_array($replay))
//        {
//            $response['type'] = 'error';
//            $response['message'] = $replay[0];
//            
//        }
//        else
//        {
//            $sql = "UPDATE `sales_order_grid` SET `awb_link` = '".$replay."'  WHERE `increment_id` = ".$post['aramex_shipment_receiver_reference']." ";
//            $this->resourceConnection->getConnection()->query($sql);
//            $response['type'] = 'success';
//            $response['message'] = 'AWB generated successfully';
//        }
        if(count($replay) == 3)
        {
            $response['type'] = 'error';
            $response['message'] = $replay[0];
            
        }
        else
        {
            $sql = "UPDATE `sales_order_grid` SET `awb_link` = '".$replay[0]."'  WHERE `increment_id` = ".$post['aramex_shipment_receiver_reference']." ";
            $this->resourceConnection->getConnection()->query($sql);
            
            $aramexWayBillNo = $replay[1];
            
            // update sales_order_grid
            $updateOrderGridTable = "UPDATE `sales_order_grid` SET `aramex_waybill_number` = '".$aramexWayBillNo."'  WHERE `increment_id` = ".$post['aramex_shipment_receiver_reference']." ";
            $this->resourceConnection->getConnection()->query($updateOrderGridTable);
            
            // update sales_order
            $updateOrderTable = "UPDATE `sales_order` SET `aramex_waybill_number` = '".$aramexWayBillNo."'  WHERE `increment_id` = ".$post['aramex_shipment_receiver_reference']." ";
            $this->resourceConnection->getConnection()->query($updateOrderTable);
            
            $response['type'] = 'success';
            $response['message'] = 'AWB generated successfully';
            //$response['aramexAWB'] = $aramexWayBillNo;
            
        }
        return $response;
        

    }

    public function getTotalWeight($itemvv) {
        if ($itemvv->getWeight() != 0) {
            $weight = $itemvv->getWeight() * $itemvv->getQtyOrdered();
        } else {
            $weight = 0.5 * $itemvv->getQtyOrdered();
        }
        return $weight;
    }

    public function getTotalItems($itemsv, $isShipped) {
        $post = [];
        foreach ($itemsv as $item) {
            if ($item->getQtyOrdered() > $item->getQtyShipped() or $isShipped) {
                $_qty = abs($item->getQtyOrdered() - $item->getQtyShipped());
                if ($_qty == 0 && $isShipped) {
                    $_qty = (int) $item->getQtyShipped();
                }
                $post[$item->getId()] = $_qty;
            }
        }
        return $post;
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

    public function postAction($order, $method, $post = []) {
        
        $baseUrl = $this->reader->getModuleDir('etc', 'Evince_AWBnumber') . '/wsdl/Aramex/';

        $soapClient = $this->soapClientFactory->create($baseUrl .
                'shipping-services-api-wsdl.wsdl', ['version' => SOAP_1_1, 'trace' => 1, 'keep_alive' => false]);

        $aramex_errors = false;
        $errors = [];
        try {
            /* here's your form processing */
            
            $major_par = $this->getParameters($order, $post);
            
            try {
                //create shipment call
                $auth_call = $soapClient->CreateShipments($major_par);
                
                if ($auth_call->HasErrors) {
                    $errors = $this->getErrorsText($auth_call);
                    return ([$errors, $method, 'error']);
                } else {
                    // Aramex AWB number
                    $url = $auth_call->Shipments->ProcessedShipment->ShipmentLabel->LabelURL;
                    $awb_number = $auth_call->Shipments->ProcessedShipment->ID;
                    return [$url,$awb_number];
                }
            } catch (\Exception $e) {
                $errors = $e->getMessage();
                return [$errors, $method, 'error'];
            }

        } catch (\Exception $e) {
            $errors = $e->getMessage();
            return [$errors, $method, 'error'];
        }
    }

    public function getParameters($order, $post) {

        //echo "<pre>"; var_dump($order->getData()); exit;
        $orderStoreId = $order->getData('store_id');
        $totalItems = 0;
        $items = $order->getAllItems();
        $descriptionOfGoods = '';
        foreach ($order->getAllVisibleItems() as $itemname) {
            $descriptionOfGoods .= $itemname->getId() . ' - ' . trim($itemname->getName());
        }
        $aramex_items_counter = 0;

        foreach ($post['aramex_items'] as $key => $value) {
            $aramex_items_counter++;
            if ($value != 0) {
                //itrating order items
                foreach ($items as $item) {
                    if ($item->getId() == $key) {
                        //get weight
                        if ($item->getWeight() != 0) {
                            $weight = $item->getWeight() * $item->getQtyOrdered();
                        } else {
                            $weight = 0.5 * $item->getQtyOrdered();
                        }
                        // collect items for aramex
                        $aramex_items[] = [
                            'PackageType' => 'Box',
                            'Quantity' => $post[$item->getId()],
                            'Weight' => [
                                'Value' => $weight,
                                'Unit' => 'Kg'
                            ],
                            'Comments' => $item->getName(),
                            'Reference' => ''
                        ];
                        $totalItems += $post[$item->getId()];
                    }
                }
            }
        }

        $totalWeight = $post['order_weight'];
        $params = [];
        //shipper parameters

        $post['aramex_shipment_shipper_country'] = $this->scopeConfig->getValue('aramex/settings/account_country_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $params['Shipper'] = [
            'Reference1' => $post['aramex_shipment_shipper_reference'],
            'Reference2' => '',
            'AccountNumber' => ($post['aramex_shipment_info_billing_account'] == 1) ?
                    $post['aramex_shipment_shipper_account'] : $post['aramex_shipment_shipper_account'],
            //Party Address
            'PartyAddress' => [
                'Line1' => $post['aramex_shipment_shipper_street'],
                'Line2' => '',
                'Line3' => '',
                'City' => $post['aramex_shipment_shipper_city'],
                'StateOrProvinceCode' => $post['aramex_shipment_shipper_state'],
                'PostCode' => $post['aramex_shipment_shipper_postal'],
                'CountryCode' => $post['aramex_shipment_shipper_country'],
            ],
            //Contact Info
            'Contact' => [
                'Department' => '',
                'PersonName' => $post['aramex_shipment_shipper_name'],
                'Title' => '',
                'CompanyName' => $post['aramex_shipment_shipper_company'],
                'PhoneNumber1' => $post['aramex_shipment_shipper_phone'],
                'PhoneNumber1Ext' => '',
                'PhoneNumber2' => '',
                'PhoneNumber2Ext' => '',
                'FaxNumber' => '',
                'CellPhone' => $post['aramex_shipment_shipper_phone'],
                'EmailAddress' => $post['aramex_shipment_shipper_email'],
                'Type' => ''
            ],
        ];
//echo $post['aramex_shipment_receiver_phone']; exit;
        if($orderStoreId == "1")
        {
            // English Store View
            //consinee parameters
            $params['Consignee'] = [
                'Reference1' => $post['aramex_shipment_receiver_reference'],
                'Reference2' => '',
                'AccountNumber' => '',
                //Party Address
                'PartyAddress' => [
                    'Line1' => $post['aramex_shipment_receiver_street'],
                    'Line2' => '',
                    'Line3' => '',
                    'City' => $post['aramex_shipment_receiver_city'],
                    'StateOrProvinceCode' => '',
                    'PostCode' => $post['aramex_shipment_receiver_postal'],
                    'CountryCode' => $post['aramex_shipment_receiver_country'],
                ],
                //Contact Info
                'Contact' => [
                    'Department' => '',
                    'PersonName' => $post['aramex_shipment_receiver_name'],
                    'Title' => '',
                    'CompanyName' => $post['aramex_shipment_receiver_company'],
                    'PhoneNumber1' => $post['aramex_shipment_receiver_phone'],
                    'PhoneNumber1Ext' => '',
                    'PhoneNumber2' => '',
                    'PhoneNumber2Ext' => '',
                    'FaxNumber' => '',
                    'CellPhone' => $post['aramex_shipment_receiver_phone'],
                    'EmailAddress' => $post['aramex_shipment_receiver_email'],
                    'Type' => ''
                ]
            ];
        }
        else
        {
            // Arabic Store View
            //consinee parameters
            $params['Consignee'] = [
                'Reference1' => $post['aramex_shipment_receiver_reference'],
                'Reference2' => '',
                'AccountNumber' => '',
                //Party Address
                'PartyAddress' => [
                    'Line1' => $post['aramex_shipment_receiver_street'],
                    'Line2' => '',
                    'Line3' => '',
                    'City' => $this->getCityNameInEng($post['aramex_shipment_receiver_city']),
                    'StateOrProvinceCode' => '',
                    'PostCode' => $post['aramex_shipment_receiver_postal'],
                    'CountryCode' => $post['aramex_shipment_receiver_country'],
                ],
                //Contact Info
                'Contact' => [
                    'Department' => '',
                    'PersonName' => $post['aramex_shipment_receiver_name'],
                    'Title' => '',
                    'CompanyName' => $post['aramex_shipment_receiver_company'],
                    'PhoneNumber1' => $post['aramex_shipment_receiver_phone'],
                    'PhoneNumber1Ext' => '',
                    'PhoneNumber2' => '',
                    'PhoneNumber2Ext' => '',
                    'FaxNumber' => '',
                    'CellPhone' => $post['aramex_shipment_receiver_phone'],
                    'EmailAddress' => $post['aramex_shipment_receiver_email'],
                    'Type' => ''
                ]
            ];
        }
        
        // EVINCE CHECK DOMESTIC COURIER OR NOT
        $countryCode = $post['aramex_shipment_receiver_country'];
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
        $params['Reference1'] = $post['aramex_shipment_info_reference'];
        $params['Reference2'] = '';
        $params['Reference3'] = '';
        $params['ForeignHAWB'] = $post['aramex_shipment_info_foreignhawb'];

        $params['TransportType'] = 0;
        $params['ShippingDateTime'] = time();
        $params['DueDate'] = time() + (7 * 24 * 60 * 60);
        $params['PickupLocation'] = 'Reception';
        $params['PickupGUID'] = '';
        $params['Comments'] = $post['aramex_shipment_info_comment'];
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
                'Unit' => $post['weight_unit']
            ],
//                'ProductGroup' => $post['aramex_shipment_info_product_group'],
//                'ProductType' => $post['aramex_shipment_info_product_type'],
//                'PaymentType' => $post['aramex_shipment_info_payment_type'],
//                'PaymentOptions' => $post['aramex_shipment_info_payment_option'],
//                'Services' => $post['aramex_shipment_info_service_type'],
            'ProductGroup' => $post['aramex_shipment_info_product_group'],
            'ProductType' => $post['aramex_shipment_info_product_type'],
            'PaymentType' => 'P',
            'PaymentOptions' => '',
            'Services' => $service,
            'NumberOfPieces' => $totalItems,
            'DescriptionOfGoods' => (trim($post['aramex_shipment_description']) == '') ?
                    $descriptionOfGoods : $post['aramex_shipment_description'],
            'GoodsOriginCountry' => $post['aramex_shipment_shipper_country'],
            'Items' => $aramex_items,
        ];

        $params['Details']['CashOnDeliveryAmount'] = [
            'Value' => $cashOnDelivery,
            'CurrencyCode' => $post['aramex_shipment_currency_code']
        ];

        /*
         * 
         * Reference from Aramex magento module
         * No need of custom amount as module use custom amount in pop-up form
         * https://i.imgur.com/SQ9p9Gz.png
         * 
         */
//         $params['Details']['CustomsValueAmount'] = [
//          'Value' => $post['aramex_shipment_info_custom_amount'],
//          'CurrencyCode' => $post['aramex_shipment_currency_code']
//          ];

        $major_par['Shipments'][] = $params;
        $clientInfo = $this->helper->getClientInfo();

        //echo "<pre>";print_r($clientInfo); exit;

        $major_par['ClientInfo'] = $clientInfo;
        $major_par['LabelInfo'] = [
            'ReportID' => 9729,
            'ReportType' => 'URL'
        ];
        return $major_par;
    }

    /**
     * Gets errors description
     *
     * @param object $auth_call Feadbeck from Aramex server
     * @return string Errors description
     */
    public function getErrorsText($auth_call) {
        //echo count(get_object_vars($auth_call->Shipments));
        
        if ($auth_call->HasErrors) {
           if (empty($auth_call->Shipments)) {
               
                $errors = 'Aramex: ' . $auth_call->Notifications->Notification->Code . ' - ' .
                        $auth_call->Notifications->Notification->Message;
                
            }
            else {
                if(count(get_object_vars($auth_call->Shipments)) > 0)
                {
                    $errors = 'Aramex: ' . $auth_call->Shipments->ProcessedShipment->Notifications->
                        Notification->Code . ' - ' . $auth_call->Shipments->ProcessedShipment->
                        Notifications->Notification->Message;
                    
                }
                else
                {
                    $errors = 'Aramex: ' . $auth_call->Notifications->Notification->Code . ' - ' .
                        $auth_call->Notifications->Notification->Message;
                }
                
            }
        }
        
        //echo "<pre>"; print_r($errors); echo "</pre>"; exit;
        return $errors;
    }
    
    public function getCityNameInEng($arcity)
    {
        $connection = $this->resourceConnection->getConnection();
        // $query = "SELECT `city` FROM `courier_manager` WHERE `city_arabic` LIKE '%".$arcity."%' ";
        $query = 'SELECT `city` FROM `courier_manager` WHERE `city_arabic` = "' . $arcity . '"';
        $shipping_method = $connection->fetchOne($query);
        return $shipping_method;
        
    }

}
