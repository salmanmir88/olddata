<?php

namespace Saee\ShipmentMethod\Observer;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Saee\ShipmentMethod\Helper\SaeeUtils;
use Saee\ShipmentMethod\Model\DbDataFactory;

/**
 * Class Orderplaceafter
 * @package Saee\ShipmentMethod\Observer
 */
class Orderplaceafter implements ObserverInterface
{

    CONST CREATE_ORDER_NEW = '/deliveryrequest/new';

    /***
     * @var LoggerInterface
     */
    protected $logger;

    /***
     * @var DbDataFactory
     */
    protected $_dbDataFactory;

    /**
     * @var SaeeUtils
     */
    protected $saeeUtils;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Orderplaceafter constructor.
     * @param LoggerInterface $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param SaeeUtils $saeeUtils
     * @param DbDataFactory $dbDataFactory
     */
    public function __construct(LoggerInterface $logger,
                                ScopeConfigInterface $scopeConfig,
                                SaeeUtils $saeeUtils,
                                DbDataFactory  $dbDataFactory) {
        $this->logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->saeeUtils = $saeeUtils;
        $this->_dbDataFactory = $dbDataFactory;
    }

    public function execute(Observer $observer){
        try {
            $order = $observer->getEvent()->getOrder();


            $this->logger->info("Create Order Event Observer");
            $Street =$order->getBillingAddress()->getStreet();
            $payment_method = $order->getPayment()->getMethod();
            $cashondelivery = 0;

            if ($payment_method == "cashondelivery"){
                $cashondelivery = $order->getGrandTotal();
            }


            $this->logger->info("Payment Method:    ".$payment_method);
            $this->logger->info("Name:              ".$order->getCustomerName());
            $this->logger->info("Order ID:          ".$order->getId());
            $this->logger->info("Increment ID:      ".$order->getIncrementId());
            $this->logger->info("Order Status:      ".$order->getStatus());
            $this->logger->info("Shipping Method:   ".$order->getShippingMethod());
            $this->logger->info("Total Amount:      ".$cashondelivery);
            $this->logger->info("Telephone Number:  ".$order->getShippingAddress()->getTelephone());
            $this->logger->info("Weight:            ".$order->getWeight());
            $this->logger->info("Order Quantity:    ".$order->getTotalQtyOrdered());
            $this->logger->info("City:              ".$order->getShippingAddress()->getCity());
            $this->logger->info("Street Address:    ". $Street[0]);

            if($order->getShippingMethod()=="saeeShipping_saeeShipping" && $order->getStatus()=="pending") {
                $data = array(
                    'ordernumber'=> $order->getId(),
                    'secret' => $this->saeeUtils->getSaeeKey(),
                    'cashondelivery' => $cashondelivery,
                    'name' =>$order->getCustomerName(),
                    'mobile' => $order->getShippingAddress()->getTelephone(),
                    'streetaddress' => $Street[0],
                    'city' => $order->getShippingAddress()->getCity(),
                    'weight' => $order->getWeight(),
                    'quantity' => $order->getTotalQtyOrdered(),
                    'pickup_address_id' => $this->saeeUtils->getPickupAddressId(),
                );
                $postUrl = $this->saeeUtils->getSaeeUrl() . self::CREATE_ORDER_NEW;
                $jsonResponse = $this->saeeUtils->saeeCurlExec($postUrl,
                                                       "POST",
                                                              $data);
                $this->logger->info($jsonResponse);
                $response=json_decode($jsonResponse, true);

                $model = $this->_dbDataFactory->create();
                  $model->addData([
                      "order_id"=>$order->getId(),
                      "waybill" => $response['waybill'],
                      "status" => $order->getStatus(),
                      "message"=>$response['message']
                  ]);
                  $model->save();
            }
        }catch (Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }
}
