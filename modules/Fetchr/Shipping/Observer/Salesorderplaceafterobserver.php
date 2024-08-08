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

class Salesorderplaceafterobserver extends Fetchr_Shipping_Model_Observer implements \Magento\FrameWork\Event\ObserverInterface
{

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager,
                                \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
                                \Psr\Log\LoggerInterface $logger, Fetchr_Shipping_Model_Session $session,
                                \Magento\Sales\Model\Service\InvoiceService $invoiceService) {
        parent::__construct($objectManager, $scopeConfig, $logger, $session, $invoiceService);
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        return $this->getCODTrackingNo($observer);
    }

    public function getCODTrackingNo($observer)
    {
        //Check IF the Auto Push Is Enabled
        $autoCODPush = $this->_scopeConfig->getValue('carriers/fetchr/autocodpush');
        $order = $observer->getEvent()->getOrder();
        $paymentType = $order->getPayment()->getMethodInstance()->getCode();

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
        if ($autoCODPush == true && $paymentType == 'COD') {
            return $this->pushCODOrder($order);
        }
    }
}
