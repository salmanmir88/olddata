<?php
/**
 * Webkul Odoomagentoconnect SalesOrderPlaceAfterObserver Observer Model
 *
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Webkul Odoomagentoconnect SalesOrderShipmentAfterObserver Class
 */
class SalesOrderShipmentAfterObserver implements ObserverInterface
{
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        \Webkul\Odoomagentoconnect\Model\Order $orderModel,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Order $orderMapping,
        \Magento\Sales\Model\Order $salesModel,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_requestInterface = $requestInterface;
        $this->_connection = $connection;
        $this->_salesModel = $salesModel;
        $this->_orderModel = $orderModel;
        $this->_orderMapping = $orderMapping;
        $this->messageManager = $messageManager;
    }

    /**
     * Sale Order Shipment After event handler
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $route = $this->_requestInterface->getControllerName();
        /** @var $orderInstance Order */
        $shipmentObj = $observer->getEvent()->getShipment();
        $orderId = $observer->getEvent()->getShipment()->getOrderId();
        $helper = $this->_connection;
        $autoShipment = $helper->getStoreConfig('odoomagentoconnect/order_settings/ship_order');
        $showMessages = $helper->getStoreConfig('odoomagentoconnect/additional/show_messages');
        if ($autoShipment==1 && $orderId && in_array($route, ['order', 'order_shipment', 'order_invoice', 'manual', 'order_create', 'express'])) {
            $mappingcollection = $this->_orderModel
                ->getCollection()
                ->addFieldToFilter('magento_id', ['eq'=>$orderId]);
            if ($mappingcollection->getSize() > 0) {
                $thisOrder = $this->_salesModel->load($orderId);
                $helper->getSocketConnect();
                $userId = $helper->getSession()->getUserId();
                if ($userId > 0) {
                    foreach ($mappingcollection as $map) {
                        $odooId = (int)$map->getOdooId();
                        if ($odooId > 0) {
                            $odooName = $map->getOdooOrder();
                            $status = $this->_orderMapping
                                           ->checkOdooOrderStatus($odooId, 'is_shipped');
                            if (!$status) {
                                $inv = $this->_orderMapping
                                            ->deliverOdooOrder($thisOrder, $odooId, $shipmentObj);
                                if ($showMessages){
                                    if (!$inv) {
                                        $message = "Odoo Order ".$odooName." Not Shipped, check Log for more details!!!";
                                        $this->messageManager->addError(__($message));
                                    } else {
                                        $message = "Odoo Order ".$odooName." Successfully Shipped.";
                                        $this->messageManager->addSuccess(__($message));
                                    }
                                }
                            } elseif ($status == true && $showMessages) {
                                $message = "Odoo Order ".$odooName." is Already Delivered.";
                                $this->messageManager->addNotice(__($message));
                            } elseif ($showMessages) {
                                $message = "Odoo Order ".$odooName." Not Shipped, check Log for more details!!!";
                                $this->messageManager->addError(__($message));
                            }
                        }
                    }
                }
            }
        }
    }
}
