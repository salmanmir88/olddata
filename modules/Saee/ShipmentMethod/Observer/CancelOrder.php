<?php

namespace Saee\ShipmentMethod\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Saee\ShipmentMethod\Helper\SaeeUtils;
use Saee\ShipmentMethod\Model\DbDataFactory;


/**
 * Class CancelOrder
 * @package Saee\ShipmentMethod\Observer
 */
class CancelOrder implements ObserverInterface
{

    CONST CANCEL_ORDER = '/deliveryrequest/cancel';

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var DbDataFactory
     */
    protected $_dbDataFActory;

    /**
     * @var SaeeUtils
     */
    protected $saeeUtils;

    /**
     * CancelOrder constructor.
     * @param LoggerInterface $logger
     * @param SaeeUtils $saeeUtils
     * @param DbDataFactory $dbDataFactory
     */
    public function __construct(LoggerInterface $logger,
                                SaeeUtils $saeeUtils,
                                DbDataFactory  $dbDataFactory) {
        $this->logger = $logger;
        $this->_dbDataFActory = $dbDataFactory;
        $this->saeeUtils = $saeeUtils;
    }

    public function execute(Observer $observer){
        try {
            $order = $observer->getEvent()->getOrder();
            $this->logger->info("Cancel Order Event Observer");

            $Street =$order->getBillingAddress()->getStreet();

            $this->logger->info("Name:              ".$order->getCustomerName());
            $this->logger->info("Order ID:          ".$order->getId());
            $this->logger->info("Increment ID:      ".$order->getIncrementId());
            $this->logger->info("Order Status:      ".$order->getStatus());
            $this->logger->info("Shipping Method:   ".$order->getShippingMethod());
            $this->logger->info("Total Amount:      ".$order->getGrandTotal());
            $this->logger->info("Telephone Number:  ".$order->getShippingAddress()->getTelephone());
            $this->logger->info("Weight:            ".$order->getWeight());
            $this->logger->info("Order Quantity:    ".$order->getTotalQtyOrdered());
            $this->logger->info("City:              ".$order->getShippingAddress()->getCity());
            $this->logger->info("Street Address:    ". $Street[0]);

            if($order->getShippingMethod()=="saeeShipping_saeeShipping") {
                $waybill = $this->saeeUtils->getSaeeWaybill($order->getID());
                $data = array(
                    'secret' => $this->saeeUtils->getSaeeKey(),
                    'waybill' =>$waybill,
                );

                $cancelUrl = $this->saeeUtils->getSaeeUrl() . self::CANCEL_ORDER;

                $jsonResponse = $this->saeeUtils->saeeCurlExec($cancelUrl,
                                                       "POST",
                                                              $data);
                $this->logger->info($jsonResponse);
                $model = $this->_dbDataFActory->create();
                if($jsonResponse==1){
                    $message="Order canceled successfully";
                }else{
                    $message="Failed to cancel order";
                }
                $model->addData([
                    "waybill" =>$waybill,
                    "order_id"=>$order->getId(),
                    "status" => $order->getStatus(),
                    "message"=>$message
                ]);
                $model->save();
            }

        }catch (Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }
}
