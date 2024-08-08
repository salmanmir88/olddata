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
 * Webkul Odoomagentoconnect SalesOrderCancelObserver Class
 */
class SalesOrderCancelObserver implements ObserverInterface
{
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        \Webkul\Odoomagentoconnect\Model\Order $orderModel,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Order $orderMapping,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_requestInterface = $requestInterface;
        $this->_connection = $connection;
        $this->_orderModel = $orderModel;
        $this->_orderMapping = $orderMapping;
        $this->messageManager = $messageManager;
    }

    /**
     * Sale Order Cancel event handler
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $route = $this->_requestInterface->getControllerName();
        $operationFrom = $this->_connection->getSession()->getOperationFrom();
        $this->_connection->getSession()->unsOperationFrom();
        if ($operationFrom == 'odoo') {
            return true;
        }
        $helper = $this->_connection;
        $orderId = $observer->getOrder()->getId();

        $mappingcollection = $this->_orderModel->getCollection()
                                        ->addFieldToFilter('magento_id', ['eq'=>$orderId]);
        if ($mappingcollection->getSize() > 0) {
            $helper->getSocketConnect();
            $userId = $helper->getSession()->getUserId();
            $context = $helper->getOdooContext();
            if ($userId > 0) {
                foreach ($mappingcollection as $map) {
                    $odooId = (int)$map->getOdooId();
                    $orderName = $map->getOdooOrder();
                    if ($odooId > 0) {
                        $status = $this->_orderMapping
                                       ->checkOdooOrderStatus($odooId, 'state');
                        if ($status != 'cancel') {
                            $resp = $helper->callOdooMethod('wk.skeleton', 'set_order_cancel', [$odooId], true);
                            if ($resp && $resp[0]) {
                                $message = " Canceled Successfully.";
                                $this->messageManager->addSuccess(__("Odoo Order ".$orderName.$message));
                            } else {
                                $faultString = $resp[1];
                                $error = 'Sync Error, Odoo Order '.$orderName.', During Cancel: , '.$faultString;
                                $helper->addError($error);
                                $this->messageManager->addError(__($error));
                            }
                        }
                    }
                }
            } else {
                $message = "Odoo Order Cannot be canceled, Your Magento Is Not Connected with Odoo. Error, ";
                $this->messageManager->addError(__($message.$userId));
            }
        } else {
            $message = "Odoo Order Cannot be Canceled. Because, Order is Not Yet Synced at Odoo!!!";
            $this->messageManager->addError(__($message));
        }
    }
}
