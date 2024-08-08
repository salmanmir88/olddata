<?php

/**
 * Fetchr
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * https://fetchr.zendesk.com/hc/en-us/categories/200522821-Downloads
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to ws@fetchr.us so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Fetchr Magento Extension to newer
 * versions in the future. If you wish to customize Fetchr Magento Extension (Fetchr Shipping) for your
 * needs please refer to http://www.fetchr.us for more information.
 *
 * @author     Danish Kamal, Feiran Wang
 * @package    Fetchr Shipping
 * Used in creating options for fulfilment|delivery config value selection
 * @copyright  Copyright (c) 2018 Fetchr (http://www.fetchr.us)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Fetchr_Shipping_Model_Observer
{

    protected $_objectManager;
    protected $_scopeConfig;
    protected $_logger;
    protected $_session;
    protected $_invoiceService;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager,
                                \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
                                \Psr\Log\LoggerInterface $logger, Fetchr_Shipping_Model_Session $session, 
                                \Magento\Sales\Model\Service\InvoiceService $invoiceService) {
        
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_logger = $logger;
        $this->_session = $session;
        $this->_invoiceService = $invoiceService;
    }

    protected function pushCODOrder($order, $shipment = '')
    {
        // $collection = $this->_objectManager->create('Magento\Sales\Api\Data\OrderInterface')->loadByIncrementId($order->getIncrementId());
        // $collection = $this->_orderFactory->create()->loadByIncrementId($order->getIncrementId());
        $shippingmethod = $order->getShippingMethod();
        $paymentType = 'COD';

        // Get the selected shipping methods from the config of Fetchr Shipping
        // And Include them as they are fethcr. Refer to ---> https://docs.google.com/document/d/1oUosCu2at0U7rWCg24cN-gZHwfdCPPcIgkd6APHMthQ/edit?ts=567671b3
        $activeShippingMethods = $this->_scopeConfig->getValue('carriers/fetchr/activeshippingmethods');
        $activeShippingMethods = explode(',', $activeShippingMethods);

        if ($order && $paymentType == 'COD') {
            //Get the selected Fetchr Shipping method and put it in the datERP comment
            $shippingmethod = explode('_', $shippingmethod);

            if (in_array($shippingmethod[0], $activeShippingMethods) || $shippingmethod[0] == 'fetchr') {
                $selectedShippingMethod = implode(" ", $shippingmethod);

                try {
                    $totalItemCount = 0;
                    foreach ($order->getAllVisibleItems() as $item) {

                        $totalItemCount = $totalItemCount + $item['qty_ordered'];

                        //Replace Special characters in the items name
                        $item['name'] = strtr($item['name'], array('"' => ' Inch ', '&' => ' And '));

                        //Get Shipment ID when its not empty
                        if (!empty($shipment)) {
                            $shipmentColl = $order->getShipmentsCollection()->getFirstItem();
                            $shipmentId = $shipmentColl->getIncrementId();
                        }

                        if ($item['product_type'] == 'bundle') {
                            $product = $this->_objectManager->create('catalog/product')->load($item->getProductId());
                            $parentSku = $product->getSku();
                            $skuArray = explode($parentSku . '-', $item['sku']);
                            $childSku = $skuArray[1];

                            $itemArray[] = array(
                                'name' => $item['name'],
                                'sku' => $childSku,
                                'quantity' => intval($item['qty_ordered']),
                                'price_per_unit' => $item->getPriceInclTax(),
                            );
                        } elseif ($item['product_type'] == 'configurable') {
                            $itemArray[] = array(
                                'name' => $item['name'],
                                'sku' => $item['sku'],
                                'quantity' => intval($item['qty_ordered']),
                                'price_per_unit' => $item->getPriceInclTax(),
                            );
                        } else {
                            $itemArray[] = array(
                                'name' => $item['name'],
                                'sku' => $item['sku'],
                                'quantity' => intval($item['qty_ordered']),
                                'price_per_unit' => $item->getPriceInclTax(),
                            );
                        }
                    }

                    $discountAmount = 0;
                    if ($order->getDiscountAmount()) {
                        $discountAmount = abs($order->getDiscountAmount()) + $order->getRewardpointsDiscount();
                    }

                    $address = $order->getShippingAddress()->getData();
                    $customer_country = $this->_objectManager->create('\Magento\Directory\Model\Country')->load($address['country_id'])->getName();
                    $discount = $discountAmount;

                    $this->serviceType = $this->_scopeConfig->getValue('carriers/fetchr/servicetype');
                    $this->token = $this->_scopeConfig->getValue('carriers/fetchr/token');
                    $this->address_id = $this->_scopeConfig->getValue('carriers/fetchr/addressid');
                    $ServiceType = $this->serviceType;

                    //Handling Special chars in the address
                    foreach ($address as $key => $value) {
                        $address[$key] = strtr($address[$key], array('"' => ' ', '&' => ' And '));
                    }

                    $erpOrderId = $this->address_id . '_' . $order->getIncrementId();

                    switch ($ServiceType) {
                        case 'fulfilment':
                            $dataErp = array(
                                'data' => array(
                                    array(
                                        'items' => $itemArray,
                                        'warehouse_location' => array(
                                            'id' => $this->address_id,
                                        ),
                                        'details' => array(
                                            'extra_fee' => $order->getShippingAmount(),
                                            'discount' => $discount,
                                            'customer_email' => $order->getCustomerEmail(),
                                            'order_reference' => $erpOrderId,
                                            'customer_name' => $address['firstname'] . ' ' . $address['lastname'],
                                            'payment_type' => $paymentType,
                                            'customer_phone' => $address['telephone'],
                                            'customer_city' => $address['city'],
                                            'customer_country' => $customer_country,
                                            'customer_address' => $address['street'],
                                        ),
                                    )
                                ),
                            );
                            break;
                        case 'delivery':
                            $dataErp = array(
                                'client_address_id' => $this->address_id,
                                'data' => array(
                                    array(
                                        'order_reference' => $erpOrderId,
                                        'name' => $address['firstname'] . ' ' . $address['lastname'],
                                        'email' => $order->getCustomerEmail(),
                                        'phone_number' => $address['telephone'],
                                        'address' => $address['street'],
                                        'receiver_city' => $address['city'],
                                        'receiver_country' => $customer_country,
                                        'payment_type' => $paymentType,
                                        'total_amount' => $order->getGrandTotal(),
                                        'description' => 'No',
                                        'bag_count' => $this->_scopeConfig->getValue('carriers/fetchr/productbagcount') ? $totalItemCount : 1,
                                        'comments' => $selectedShippingMethod,
                                    ),
                                ),
                            );
                    }

                    //Check if order already pushed
                    $orderIsPushed = $this->_checkIfOrderIsPushed($this->address_id . '_' . $order->getIncrementId());

                    if ($orderIsPushed['error_code'] == 1151) {
                        $result[$order->getIncrementId()]['request_data'] = $dataErp;
                        $result[$order->getIncrementId()]['response_data'] = $this->_sendDataToErp($dataErp, $order->getIncrementId());

                        $response = $result[$order->getIncrementId()]['response_data'];
                        $comments = '[Pushed] trace_id: ' . $response['trace_id'] . '. ';

                        if (!is_array($response)) {
                            $response = explode('.', $response);
                            $comments .= '<strong>Fetchr Status:Failed,</strong> Order was <strong>NOT</strong> pushed due to <strong>' . $response[0] . '</strong> Error, Please contact one of <strong>Fetchr\'s</strong> account managers and try again later';
                            $order->setStatus('pending');
                            $order->addStatusHistoryComment($comments, false);
                        } else if ($response['status'] == 'error') {
                            $comments .= '<strong>Fetchr Status:Failed,</strong> Order was <strong>NOT</strong> pushed due to <strong>' . $response['message'] . '</strong> Error, Please contact one of <strong>Fetchr\'s</strong> account managers and try again later';
                            $order->setStatus('pending');
                            $order->addStatusHistoryComment($comments, false);
                        } else if ($response['data'][0]['status'] == 'error') {
                            $comments .= '<strong>Fetchr Status:Failed,</strong> Order was <strong>NOT</strong> pushed due to <strong>' . $response['data'][0]['message'] . '</strong> Error, Please contact one of <strong>Fetchr\'s</strong> account managers and try again later';
                            $order->setStatus('pending');
                            $order->addStatusHistoryComment($comments, false);
                        } else {
                            // Setting The Comment in the Order view
                            $tracking_number = $response['data'][0]['tracking_no'];
                            $awb_link = isset($response['data'][0]['awb_link']) ? $response['data'][0]['awb_link'] : "empty awb_link";
                            $comments .= '<strong>Fetchr Status:Success,</strong> Order is <strong>Pushed</strong> on <strong>Fetchr</strong> ERP system, the Tracking URL is : https://track.fetchr.us/track/' . $tracking_number . '. AWB link: ' . $awb_link;
                            $order->setState(Magento\Sales\Model\Order::STATE_PROCESSING, true);
                            $order->setStatus('processing');
                            $order->addStatusHistoryComment($comments, false);
                            $order->save();
                        }

                        //COD Order Shipping And Invoicing
                        if ($response['data'][0]['status'] == 'success') {
                            // $this->_session->setOrderIsPushed(true);
                            try {

                                //Get Order Qty
                                $qty = array();
                                foreach ($order->getAllVisibleItems() as $item) {
                                    $product_id = $item->getProductId();
                                    $Itemqty = $item->getQtyOrdered() - $item->getQtyShipped() - $item->getQtyRefunded() - $item->getQtyCanceled();
                                    $qty[$item->getId()] = $Itemqty;
                                }

                                //Invoicing
                                if ($order->canInvoice()) {
                                    $invoice = $this->_invoiceService->prepareInvoice($order);
                                    $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
                                    //$invoice->setState(Mage_Sales_Model_Order_Invoice::STATE_OPEN)->save();
                                    $invoice->register();

                                    $order->setTotalPaid(0)
                                        ->setBaseTotalPaid(0)
                                        ->save();

                                    $invoice->setState(1)
                                        ->save();

                                    $transactionSave = $this->_objectManager->create('core/resource_transaction')
                                        ->addObject($invoice)
                                        ->addObject($invoice->getOrder());

                                    $transactionSave->save();

                                    //To count the order in the sales order report(link in My5)
                                    $order->setBaseTotalInvoiced('0.0000');
                                    $order->setBaseTotalDue($order->getBaseGrandTotal());

                                    $this->_logger->info('Order ' . $orderId . ' has been invoiced!', [], 'fetchr.log');
                                } else {
                                    $this->_logger->info('Order ' . $orderId . ' cannot be invoiced!', [], 'fetchr.log');
                                }

                                //Create Shipment When Auto Push Is OFF
                                if (!empty($shipment)) {
                                    $trackdata = array();
                                    $trackdata['carrier_code'] = 'fetchr';
                                    $trackdata['title'] = 'Fetchr';
                                    $trackdata['number'] = $tracking_number;

                                    $track = $this->_objectManager->create('sales/order_shipment_track')->addData($trackdata);
                                    $shipment->addTrack($track);
                                } else {
                                    //Create Shipment When Auto Push Is ON
                                    if ($order->canShip()) {
                                        $shipment = $order->prepareShipment($qty);

                                        $trackdata = array();
                                        $trackdata['carrier_code'] = 'fetchr';
                                        $trackdata['title'] = 'Fetchr';
                                        $trackdata['url'] = 'https://track.fetchr.us/track/' . $tracking_number;
                                        $trackdata['number'] = $tracking_number;
                                        $track = $this->_objectManager->create('sales/order_shipment_track')->addData($trackdata);

                                        $shipment->addTrack($track);
                                        $shipment->register();
                                        $transactionSave = $this->_objectManager->create('core/resource_transaction')
                                            ->addObject($shipment)
                                            ->addObject($shipment->getOrder())
                                            ->save();

                                        $this->_logger->info('Order ' . $orderId . ' has been shipped!', [], 'fetchr.log');
                                    } else {
                                        $this->_logger->info('Order ' . $orderId . ' cannot be shipped!', [], 'fetchr.log');
                                    }
                                }
                            } catch (Exception $e) {
                                $order->addStatusHistoryComment('Exception occurred during automaticallyInvoiceShipCompleteOrder action. Exception message: ' . $e->getMessage(), false);
                                $order->save();
                            }
                        }
                        //End COD Order Shipping And Invoicing
                        unset($dataErp, $itemArray);
                    } else if ($orderIsPushed['status'] == 'error') {
                        $comments = '[No push] trace_id: ' . $orderIsPushed['trace_id'] . '. ';
                        $comments .= '<strong>Fetchr Status:Failed,</strong> Order was <strong>NOT</strong> pushed due to <strong>' . $orderIsPushed['message'] . '</strong>, Please contact one of <strong>Fetchr\'s</strong> account managers and try again later ';
                        $order->setStatus('pending');
                        $order->addStatusHistoryComment($comments, false);
                    } else {
                        $comments = '[No push] trace_id: ' . $orderIsPushed['trace_id'] . '. ';
                        $comments .= '<strong>Fetchr Status:Failed,</strong> Order was <strong>NOT</strong> pushed due to <strong>the order already exists in Fetchr</strong>, Please contact one of <strong>Fetchr\'s</strong> account managers and try again later ';
                        $order->setStatus('pending');
                        $order->addStatusHistoryComment($comments, false);
                    }

                } catch (Exception $e) {
                    echo (string)$e->getMessage();
                }
            }

        }
    }

    protected function pushCCOrder($order, $shipment = '', $paymentType = '')
    {
        // $collection = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($order->getIncrementId());
        $shippingmethod = $order->getShippingMethod();

        // Get the selected shipping methods from the config of Fetchr Shipping
        // And Include them as they are fethcr. Refer to ---> https://docs.google.com/document/d/1oUosCu2at0U7rWCg24cN-gZHwfdCPPcIgkd6APHMthQ/edit?ts=567671b3
        $activeShippingMethods = $this->_scopeConfig->getValue('carriers/fetchr/activeshippingmethods');
        $activeShippingMethods = explode(',', $activeShippingMethods);

        if ($order && $paymentType == 'cd') {
            // $resource = $this->_objectManager->get('core/resource');
            // $adapter = $resource->getConnection('core_read');

            $shippingmethod = explode('_', $shippingmethod);

            if (in_array($shippingmethod[0], $activeShippingMethods) || $shippingmethod[0] == 'fetchr') {
                $selectedShippingMethod = implode(" ", $shippingmethod);
                try {
                    $totalItemCount = 0;
                    foreach ($order->getAllVisibleItems() as $item) {

                        $totalItemCount = $totalItemCount + $item['qty_ordered'];

                        //Hnadling Special characters in the items name
                        $item['name'] = strtr($item['name'], array('"' => ' Inch ', '&' => ' And '));

                        if ($item['product_type'] == 'bundle') {
                            $product = $this->_objectManager->create('catalog/product')->load($item->getProductId());
                            $parentSku = $product->getSku();
                            $skuArray = explode($parentSku . '-', $item['sku']);
                            $childSku = $skuArray[1];

                            $itemArray[] = array(
                                'name' => $item['name'],
                                'sku' => $childSku,
                                'quantity' => intval($item['qty_ordered']),
                                'price_per_unit' => $item->getPriceInclTax(),
                            );
                        } elseif ($item['product_type'] == 'configurable') {
                            $itemArray[] = array(
                                'name' => $item['name'],
                                'sku' => $item['sku'],
                                'quantity' => intval($item['qty_ordered']),
                                'price_per_unit' => $item->getPriceInclTax(),
                            );
                        } else {
                            $itemArray[] = array(
                                'name' => $item['name'],
                                'sku' => $item['sku'],
                                'quantity' => intval($item['qty_ordered']),
                                'price_per_unit' => $item->getPriceInclTax(),
                            );
                        }
                    }

                    $discountAmount = 0;
                    if ($order->getDiscountAmount()) {
                        $discountAmount = abs($order->getDiscountAmount());
                    }

                    $address = $order->getShippingAddress()->getData();
                    $customer_country = $this->_objectManager->create('\Magento\Directory\Model\Country')->load($address['country_id'])->getName();
                    $discount = $discountAmount;

                    $this->serviceType = $this->_scopeConfig->getValue('carriers/fetchr/servicetype');
                    $this->address_id = $this->_scopeConfig->getValue('carriers/fetchr/addressid');
                    $this->token = $this->_scopeConfig->getValue('carriers/fetchr/token');
                    $ServiceType = $this->serviceType;

                    //Handling Special chars in the address
                    foreach ($address as $key => $value) {
                        $address[$key] = strtr($address[$key], array('"' => ' ', '&' => ' And '));
                    }

                    $erpOrderId = $this->address_id . '_' . $order->getIncrementId();

                    switch ($ServiceType) {
                        case 'fulfilment':
                            $dataErp = array(
                                'data' => array(
                                    array(
                                        'items' => $itemArray,
                                        'warehouse_location' => array(
                                            'id' => $this->address_id,
                                        ),
                                        'details' => array(
                                            'extra_fee' => $order->getShippingAmount(),
                                            'discount' => $discount,
                                            'customer_email' => $order->getCustomerEmail(),
                                            'order_reference' => $erpOrderId,
                                            'customer_name' => $address['firstname'] . ' ' . $address['lastname'],
                                            'payment_type' => $paymentType,
                                            'customer_phone' => $address['telephone'],
                                            'customer_city' => $address['city'],
                                            'customer_country' => $customer_country,
                                            'customer_address' => $address['street']
                                        )
                                    )
                                )
                            );
                            break;
                        case 'delivery':
                            $dataErp = array(
                                'client_address_id' => $this->address_id,
                                'data' => array(
                                    array(
                                        'order_reference' => $erpOrderId,
                                        'name' => $address['firstname'] . ' ' . $address['lastname'],
                                        'email' => $order->getCustomerEmail(),
                                        'phone_number' => $address['telephone'],
                                        'address' => $address['street'],
                                        'receiver_city' => $address['city'],
                                        'receiver_country' => $customer_country,
                                        'payment_type' => $paymentType,
                                        'total_amount' => $order->getGrandTotal(),
                                        'description' => 'No',
                                        'bag_count' => $this->_scopeConfig->getValue('carriers/fetchr/productbagcount') ? $totalItemCount : 1,
                                        'comments' => $selectedShippingMethod
                                    )
                                )
                            );
                    }

                    //Check if order already pushed
                    $orderIsPushed = $this->_checkIfOrderIsPushed($this->address_id . '_' . $order->getIncrementId());

                    if ($orderIsPushed['error_code'] == 1151) {

                        $result[$order->getIncrementId()]['request_data'] = $dataErp;
                        $result[$order->getIncrementId()]['response_data'] = $this->_sendDataToErp($dataErp, $order->getIncrementId());

                        $response = $result[$order->getIncrementId()]['response_data'];
                        $comments = '[Pushed] trace_id: ' . $response['trace_id'] . '. ';

                        if (!is_array($response)) {
                            $response = explode('.', $response);
                            $comments .= '<strong>Fetchr Status:Failed,</strong> Order was NOT pushed due to ' . $response[0] . ' Error, Please contact one of Fetchr\'s account managers and try again later';
                            $order->setStatus('pending');
                            $order->addStatusHistoryComment($comments, false);
                        } else if ($response['status'] == 'error') {
                            $comments .= '<strong>Fetchr Status:Failed,</strong> Order was <strong>NOT</strong> pushed due to <strong>' . $response['message'] . '</strong> Error, Please contact one of <strong>Fetchr\'s</strong> account managers and try again later';
                            $order->setStatus('pending');
                            $order->addStatusHistoryComment($comments, false);
                        } else if ($response['data'][0]['status'] == 'error') {
                            $comments .= '<strong>Fetchr Status:Failed,</strong> Order was <strong>NOT</strong> pushed due to <strong>' . $response['data'][0]['message'] . '</strong> Error, Please contact one of <strong>Fetchr\'s</strong> account managers and try again later';
                            $order->setStatus('pending');
                            $order->addStatusHistoryComment($comments, false);
                        } else {
                            // Setting The Comment in the Order view
                            $tracking_number = $response['data'][0]['tracking_no'];
                            $awb_link = isset($response['data'][0]['awb_link']) ? $response['data'][0]['awb_link'] : "empty awb_link";
                            $comments .= '<strong>Fetchr Status:Success,</strong> Order is <strong>Pushed</strong> on <strong>Fetchr</strong> ERP system, the Tracking URL is :  https://track.fetchr.us/track/' . $tracking_number . '. AWB link: ' . $awb_link;
                            $order->setStatus('processing');
                            $order->addStatusHistoryComment($comments, false);
                        }
                        //CD Order Shipping And Invoicing
                        if ($response['data'][0]['status'] == 'success') {
                            try {
                                // $this->_session->setOrderIsPushed(true);

                                //Get Order Qty
                                $qty = array();
                                foreach ($order->getAllVisibleItems() as $item) {
                                    $product_id = $item->getProductId();
                                    $Itemqty = $item->getQtyOrdered() - $item->getQtyShipped() - $item->getQtyRefunded() - $item->getQtyCanceled();
                                    $qty[$item->getId()] = $Itemqty;
                                }

                                //Create Shipment When Auto Push Is OFF
                                if (!empty($shipment)) {
                                    $trackdata = array();
                                    $trackdata['carrier_code'] = 'fetchr';
                                    $trackdata['title'] = 'Fetchr';
                                    $trackdata['number'] = $tracking_number;

                                    $track = $this->_objectManager->create('sales/order_shipment_track')->addData($trackdata);
                                    $shipment->addTrack($track);
                                } else {
                                    //Create Shipment When Auto Push Is On
                                    if ($order->canShip()) {
                                        $shipment = $order->prepareShipment($qty);

                                        $trackdata = array();
                                        $trackdata['carrier_code'] = 'fetchr';
                                        $trackdata['title'] = 'Fetchr';
                                        $trackdata['number'] = $tracking_number;
                                        $track = $this->_objectManager->create('sales/order_shipment_track')->addData($trackdata);

                                        $shipment->addTrack($track);
                                        //$shipment->register();
                                        $transactionSave = $this->_objectManager->create('core/resource_transaction')
                                            ->addObject($shipment)
                                            ->addObject($shipment->getOrder())
                                            ->save();

                                        $this->_logger->info('Order ' . $orderId . ' has been shipped!', [], 'fetchr.log');
                                    } else {
                                        $this->_logger->info('Order ' . $orderId . ' cannot be shipped!', [], 'fetchr.log');
                                    }

                                }

                            } catch (Exception $e) {
                                $order->addStatusHistoryComment('Exception occurred during automaticallyInvoiceShipCompleteOrder action. Exception message: ' . $e->getMessage(), false);
                                $order->save();
                            }
                        }
                        //End COD Order Shipping And Invoicing
                        unset($dataErp, $itemArray);
                    } else if ($orderIsPushed['status'] == 'error') {
                        $comments = '[No push] trace_id: ' . $orderIsPushed['trace_id'] . '. ';
                        $comments .= '<strong>Fetchr Status:Failed,</strong> Order was <strong>NOT</strong> pushed due to <strong>' . $orderIsPushed['message'] . '</strong>, Please contact one of <strong>Fetchr\'s</strong> account managers and try again later ';
                        $order->setStatus('pending');
                        $order->addStatusHistoryComment($comments, false);
                    } else {
                        $comments = '[No push] trace_id: ' . $orderIsPushed['trace_id'] . '. ';
                        $comments .= '<strong>Fetchr Status:Failed,</strong> Order was <strong>NOT</strong> pushed due to <strong>the order already exists in Fetchr</strong>, Please contact one of <strong>Fetchr\'s</strong> account managers and try again later ';
                        $order->setStatus('pending');
                        $order->addStatusHistoryComment($comments, false);
                    }
                } catch (Exception $e) {
                    echo (string)$e->getMessage();
                }
            }
        }
    }

    protected function _checkIfOrderIsPushed($orderId)
    {
        $this->address_id = $this->_scopeConfig->getValue('carriers/fetchr/addressid');
        $this->token = $this->_scopeConfig->getValue('carriers/fetchr/token');
        $this->accountType = $this->_scopeConfig->getValue('carriers/fetchr/accounttype');

        switch ($this->accountType) {
            case 'live':
                $baseurl = $this->_scopeConfig->getValue('fetchr_shipping/settings/liveurl');
                break;
            case 'staging':
                $baseurl = $this->_scopeConfig->getValue('fetchr_shipping/settings/stagingurl');
        }

        try {
            $ch = curl_init();
            $url = $baseurl . '/orderservice/genericinfo/' . $orderId . '?reference_type=client_ref';

            /*
            {
                "status": "error",
                "error_code": 1151,
                "message": "The so_number was not found.",
                "trace_id": "0ecec514211b4a3ebc9b93ea42bce6b0"
            }

            {
                "status": "success",
                "data": {
                    "tracking_no": "34178517634630",
                    "client_ref": "199619721aabb12345"
                },
                "message": "The so_number was found.",
                "trace_id": "eb480935f9034340a97accd7652451d1"
            }
            */
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Authorization: ' . $this->token,
                'xcaller: magento_v2',
                'xcaller-version: v2.1',
            ));

            $results = curl_exec($ch);
            $results = json_decode($results, true);
            return $results;

        } catch (Exception $e) {
            echo (string)$e->getMessage();
        }
    }

    protected function _sendDataToErp($data, $orderId)
    {
        $response = null;

        try {
            $this->accountType = $this->_scopeConfig->getValue('carriers/fetchr/accounttype');
            $this->serviceType = $this->_scopeConfig->getValue('carriers/fetchr/servicetype');
            $this->address_id = $this->_scopeConfig->getValue('carriers/fetchr/addressid');
            $this->token = $this->_scopeConfig->getValue('carriers/fetchr/token');

            $ServiceType = $this->serviceType;
            $accountType = $this->accountType;
            switch ($accountType) {
                case 'live':
                    $baseurl = $this->_scopeConfig->getValue('fetchr_shipping/settings/liveurl');
                    break;
                case 'staging':
                    $baseurl = $this->_scopeConfig->getValue('fetchr_shipping/settings/stagingurl');
            }
            switch ($ServiceType) {
                case 'fulfilment':
                    $data_string = json_encode($data);
                    $ch = curl_init();
                    $url = $baseurl . '/fulfillment';
                    curl_setopt($ch, CURLOPT_URL, $url);
                    // set POST method
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POST, true);
                    // set POST body
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                    // set POST headers
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Authorization: ' . $this->token,
                        'xcaller: magento_v2',
                        'xcaller-version: v2.1',
                    ));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                    $response = curl_exec($ch);
                    curl_close($ch);

                    $decoded_response = json_decode($response, true);

                    // validate response
                    if (!is_array($decoded_response)) {
                        return $response;
                    }

                    return $decoded_response;
                    break;
                case 'delivery':
                    $data_string = json_encode($data);
                    $ch = curl_init();
                    $url = $baseurl . '/order';

                    curl_setopt($ch, CURLOPT_URL, $url);
                    // set POST method
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                    curl_setopt($ch, CURLOPT_POST, true);
                    // set POST body
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
                    // set POST headers
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: application/json',
                        'Authorization: ' . $this->token,
                        'xcaller: magento_v2',
                        'xcaller-version: v2.1',
                    ));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                    $response = curl_exec($ch);
                    curl_close($ch);

                    // validate response
                    $decoded_response = json_decode($response, true);
                    if (!is_array($decoded_response)) {
                        return $response;
                    }

                    $response = $decoded_response;

                    $this->_logger->info('Order ' . $orderId . ' has been pushed!', [], 'fetchr.log');
                    $this->_logger->info('Order data: ' . print_r($data, true), [], 'fetchr.log');
                    $this->_logger->info('Order data: ' . $data_string, [], 'fetchr.log');
                    $this->_logger->info('Order response: ' . print_r($response, true), [], 'fetchr.log');
                    return $response;
                    break;
            }
        } catch (Exception $e) {
            echo (string)$e->getMessage();
        }
    }
}
