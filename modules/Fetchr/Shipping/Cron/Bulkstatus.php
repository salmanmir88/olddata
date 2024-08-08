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

class Fetchr_Shipping_Model_Ship_Bulkstatus
{

    protected $_objectManager;
    protected $_scopeConfig;
    protected $_logger;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager,
                                \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
                                \Psr\Log\LoggerInterface $logger) {
        $this->_objectManager = $objectManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_logger = $logger;
    }

    public function run($force_order_update=false)
    {
    $this->_logger->info('Get bulk status started!', [], 'fetchr.log');
        if(!$this->_scopeConfig->getValue('carriers/fetchr/active'))
            return;
        // if(!$force_order_update) {
        //     if(Mage::getStoreConfig('fetchr_shipping/settings/order_push') == '')
        //         return;
        // }
        $this->accountType = $this->_scopeConfig->getValue('carriers/fetchr/accounttype');
        $accountType = $this->accountType;

        switch ($accountType) {
          case 'live':
          $baseurl = $this->_scopeConfig->getValue('fetchr_shipping/settings/liveurl');
          break;
          case 'staging':
          $baseurl = $this->_scopeConfig->getValue('fetchr_shipping/settings/stagingurl');
        }

        $this->address_id     = $this->_scopeConfig->getValue('carriers/fetchr/addressid');
        $this->token     = $this->_scopeConfig->getValue('carriers/fetchr/token');

        $collection   =   $this->_objectManager->create('Magento\Sales\Model\Order')->getCollection()->addFieldToFilter('main_table.status', array(
                                array(
                                    'nin' => array(
                                        'complete',
                                        'closed',
                                        'canceled'
                                    ),
                                ),
                            ));

        $result = $tracking_numbers = $order_tracking_numbers  = array();

        if ($collection->getData()) {
            //echo "<pre>";print_r($collection->getData());die;
            foreach ($collection as $value) {
                $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($value->getId());
                foreach($order->getShipmentsCollection() as $shipment) {
                    foreach($shipment->getAllTracks() as $tracknum) {
                        //echo $tracknum->getNumber().'<br />';
                        $order_tracking_numbers[$value->getId()][] = $tracknum->getNumber();
                    }
                }
            }
        }

        foreach ($order_tracking_numbers as $otn) {
            $tracking_numbers[] = end($otn);
        }

        $data   =   array(
                'method' => 'get_status_bulk',
                'data' =>  $tracking_numbers
                );

        $data_string  = json_encode($data) ;
        $ch           = curl_init();
        $url          = $baseurl.'/plugin/status';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: '.$this->token,
            'xcaller: magento_v2',
            'xcaller-version: v2.1',
        ));
        $results = curl_exec($ch);
        $results = json_decode($results, true);

        $orderComments  = array();
        foreach ($results['data'] as $result) {
            $erpStatus  = $result['package_state'];
            $orderId    = $result['client_order_ref'];

            //Check if the order ID has client username as prefix
            if(strpos($orderId, '_') !== false){
              $oids     = explode('_', $orderId);
              $orderId  = end($oids);
            }

            $order      = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($orderId);
            $comments   = $order->getStatusHistoryCollection();

            //Get All The Comments
            foreach ($comments as $c) {
                $orderComments[$orderId][]    = $c->getData();
            }

            //Get Fetchr Comments Only
            foreach ($orderComments[$orderId] as $key => $comment) {
                $sw_fetchr    = strpos($comment['comment'], 'Fetchr');
                if($sw_fetchr != false){
                    $fetchrComments[$orderId][] = $comment;
                }
            }

            $lastFetchrComment  = $fetchrComments[$orderId][0]['comment'];
            $status_mapping = array(
                                'Scheduled for delivery',
                                'Order dispatched',
                                'Returned to Client',
                                'Customer care On hold',
                                );

            $statusdiff     = strpos($lastFetchrComment, $erpStatus);
            $paymentType    = $order->getPayment()->getMethodInstance()->getCode();

            if(strstr($erpStatus, 'Delivered') && $lastFetchrComment != null){
                $deliveryDate = explode(' ', $erpStatus);

                $order->setData('state', Magento\Sales\Model\Order::STATE_COMPLETE);
                $order->setStatus('complete')->save();
                $order->addStatusHistoryComment('<strong>Delivered By Fetchr On: </strong>'.$deliveryDate[2], false)->save();

                if($paymentType == 'cashondelivery' || $paymentType == 'phoenix_cashondelivery'){
                    $order->setBaseTotalInvoiced($order->getBaseGrandTotal());
                    $order->setBaseTotalPaid($order->getBaseGrandTotal());
                    $order->setTotalPaid($order->getBaseGrandTotal());
                }
                $order->save();

                foreach ($order->getInvoiceCollection() as $inv) {
                    $inv->setState(\Magento\Sales\Model\Order\Invoice::STATE_PAID)->save();
                }
            }elseif($erpStatus != 'Order Created' && $statusdiff === false ){
                $order->setStatus('processing')->save();
                $order->addStatusHistoryComment('trace_id: '.$results['trace_id'].'. <strong>Fetchr Status: </strong>'.$erpStatus, false)->save();
            }
        }
    $this->_logger->info('Get bulk status completed', [], 'fetchr.log');
    return $results;
    }
}
