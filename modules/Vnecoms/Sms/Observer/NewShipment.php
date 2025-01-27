<?php
namespace Vnecoms\Sms\Observer;

use Magento\Framework\Event\ObserverInterface;

class NewShipment implements ObserverInterface
{
    /**
     * @var \Vnecoms\Sms\Helper\Data
     */
    protected $helper;
    
    /**
     * @var \Magento\Email\Model\Template\Filter
     */
    protected $filter;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;
    
    /**
     * @var \Vnecoms\Sms\Model\ResourceModel\Sms\CollectionFactory
     */
    protected $smsCollectionFactory;
    
    /**
     * @param \Vnecoms\Sms\Helper\Data $helper
     * @param \Magento\Email\Model\Template\Filter $filter
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Vnecoms\Sms\Model\ResourceModel\Sms\CollectionFactory $smsCollectionFactory
     */
    public function __construct(
        \Vnecoms\Sms\Helper\Data $helper,
        \Magento\Email\Model\Template\Filter $filter,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Vnecoms\Sms\Model\ResourceModel\Sms\CollectionFactory $smsCollectionFactory
    ){
        $this->helper = $helper;
        $this->filter = $filter;
        $this->customerFactory = $customerFactory;
        $this->smsCollectionFactory = $smsCollectionFactory;
    }
    
    /**
     * Vendor Save After
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return self
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if(!$this->helper->getCurrentGateway()) return;
        
        $shipment   = $observer->getShipment();
        $order      = $shipment->getOrder();
        $additionalData = 'shipment|'.$shipment->getId();
        
        /* Check if the SMS is sent already*/
        $collection = $this->smsCollectionFactory->create()
            ->addFieldToFilter('additional_data',['like' => $additionalData]);
        if($collection->count()) return;
        

        /* Send notification message to customer when a new shipment is created*/
        if(
            $this->helper->canSendNewShipmentMessage($order->getStoreId())
        ) {
            $customer = $this->helper->getCustomerObjectForSendingSms($order);

            /* $trackingCodes = [];
            foreach ($shipment->getAllTracks() as $track){
                $trackingCodes[] = $track->getNumber();
            }
            $trackingCodes = implode(',', $trackingCodes);

            $variables = [
                'order' => $order,
                'shipment' => $shipment,
                'customer' => $customer,
                'tracking_code' => $trackingCodes,
            ]; */
			
			$trackingCodes = [];
            $carrierCodes = [];
            foreach ($shipment->getAllTracks() as $track){
                $trackingCodes[] = $track->getNumber();
                $carrierCodes[] = $track->getTitle();
            }
            $trackingCodes = implode(',', $trackingCodes);
            $carrierCodes 	= implode(',', $carrierCodes);
			
			
			$trackurl = '';
			if(strtolower($carrierCodes) === 'fedex'){
				$trackurl = 'https://www.fedex.com/apps/fedextrack/?action=track&trackingnumber='.$trackingCodes;
			}elseif (strtolower($carrierCodes) === 'usps') {
				$trackurl = 'https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1='.$trackingCodes;
			}elseif (strtolower($carrierCodes) === 'ups') {
				$trackurl = 'https://wwwapps.ups.com/WebTracking/returnToDetails?tracknum='.$trackingCodes;
			}elseif (strtolower($carrierCodes) === 'aramex') {
				$trackurl = 'https://www.aramex.com/track/results?ShipmentNumber='.$trackingCodes.'&mode=0';
			}elseif (strtolower($carrierCodes) === 'saeeshipping') {
				$trackurl = 'https://legacy.saeex.com/trackingpage?trackingnum='.$trackingCodes;
			}else{
				$trackurl = 'https://legacy.saeex.com/trackingpage?trackingnum='.$trackingCodes;
			}
			

            $variables = [
                'order' => $order,
                'shipment' => $shipment,
                'customer' => $customer,
                'tracking_code' => $trackingCodes,
                'tracking_url' => $trackurl,
            ];


            $message = $this->helper->getNewShipmentMessage($order->getStoreId());

            $this->sendSms($customer, $message, $variables, $additionalData);
        }

        $shipmentMessagesByShippingMethod = $this->helper->getNewShipmentMessagesByShippingMethod($order->getStoreId());
        $shipmentMessagesByShippingMethod = json_decode($shipmentMessagesByShippingMethod, true);
        /* Send new order message by payment method*/
        $method = $order->getShippingMethod();
        if(is_array($shipmentMessagesByShippingMethod)){
            $customer = $this->helper->getCustomerObjectForSendingSms($order);
            if(!$customer)
            foreach($shipmentMessagesByShippingMethod as $message){
                if(strpos($method, $message['shipping_method']) !== false) continue;
                $this->sendSms($customer, $message['message'], $variables, $additionalData);
            }
        }

    }

    /**
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Framework\DataObject $customer
     * @param string $messageTemplate
     */
    public function sendSms(
        \Magento\Framework\DataObject $customer,
        $messageTemplate,
        $variables,
        $additionalData = ''
    ) {
        $this->filter->setVariables($variables);
        $message = $this->filter->filter($messageTemplate);
        $this->helper->sendCustomerSms($customer, $message, $additionalData);
    }
}
