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
namespace Fetchr\Shipping\Observer;
use Fetchr_Shipping_Model_Observer;
use Fetchr_Shipping_Model_Session;

class Salesordershipmentsavebeforeobserver extends Fetchr_Shipping_Model_Observer implements \Magento\FrameWork\Event\ObserverInterface
{

    protected $_messageManager;
    protected $_response;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager,
                                \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
                                \Psr\Log\LoggerInterface $logger, Fetchr_Shipping_Model_Session $session,
                                \Magento\Framework\Message\ManagerInterface $messageManager, \Magento\Framework\App\Response\Http $response,
                                \Magento\Sales\Model\Service\InvoiceService $invoiceService) {
        parent::__construct($objectManager, $scopeConfig, $logger, $session, $invoiceService);
        $this->_messageManager = $messageManager;
        $this->_response = $response;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        return $this->pushOrderAfterShipmentCreation($observer);
    }

    public function pushOrderAfterShipmentCreation($observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        // $collection = $this->_objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($order->getIncrementId());
        $shippingmethod = $order->getShippingMethod();
        $paymentType = $order->getPayment()->getMethodInstance()->getCode();
        // $autoCODPush = $this->_scopeConfig->getValue('carriers/fetchr/autocodpush');
        // $autoCCPush = $this->_scopeConfig->getValue('carriers/fetchr/autoccpush');
        $this->address_id = $this->_scopeConfig->getValue('carriers/fetchr/addressid');

        // Get the selected shipping methods from the config of Fetchr Shipping
        // And Include them as they are fethcr. Refer to ---> https://docs.google.com/document/d/1oUosCu2at0U7rWCg24cN-gZHwfdCPPcIgkd6APHMthQ/edit?ts=567671b3
        $activeShippingMethods = $this->_scopeConfig->getValue('carriers/fetchr/activeshippingmethods');
        $activeShippingMethods = explode(',', $activeShippingMethods);

        if (strstr($paymentType, 'paypal')) {
            $paymentType = 'paypal';
        }
        switch ($paymentType) {
            case 'cashondelivery':
            case 'phoenix_cashondelivery':
                $paymentType = 'COD';
                break;
            case 'ccsave':
                $paymentType = 'cd';
                break;
            case 'paypal':
            default:
                $paymentType = 'cd';
                break;
        }

        $shippingmethod = explode('_', $shippingmethod);
        $orderIsPushed = $this->_session->getOrderIsPushed();
        if ($orderIsPushed == false) {
            $orderStatus = $this->_checkIfOrderIsPushed($this->address_id . '_' . $order->getIncrementId());
        } else {
            $orderStatus['status'] = 'success';
            $this->_session->unsOrderIsPushed();
        }
        if (isset($orderStatus['error_code']) && $orderStatus['error_code'] == 1520) {
            $this->_messageManager->addError('Invalid token on Fetchr configuration');
            $this->_response->setRedirect($_SERVER['HTTP_REFERER']);
            $this->_reponse->sendReponse();
            exit;
        }
    

        //Check if order already pushed
        if ($orderStatus['error_code'] == 1151) {
            if ((in_array($shippingmethod[0], $activeShippingMethods) || $shippingmethod[0] == 'fetchr') && $paymentType == 'COD') {
                return $this->pushCODOrder($order, $shipment);
            } elseif ((in_array($shippingmethod[0], $activeShippingMethods) || $shippingmethod[0] == 'fetchr') && $paymentType == 'cd') {
                return $this->pushCCOrder($order, $shipment, $paymentType);
            }
        }
    }
}
