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
 * Webkul Odoomagentoconnect SalesOrderInvoiceAfterObserver Class
 */
class SalesOrderInvoiceAfterObserver implements ObserverInterface
{
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Webkul\Odoomagentoconnect\Helper\Connection $connection,
        \Webkul\Odoomagentoconnect\Model\Order $orderMapping,
        \Webkul\Odoomagentoconnect\Model\ResourceModel\Order $orderMapResource,
        \Magento\Sales\Model\Order $orderModel,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_requestInterface = $requestInterface;
        $this->_connection = $connection;
        $this->_orderModel = $orderModel;
        $this->_orderMapping = $orderMapping;
        $this->_orderMapResource = $orderMapResource;
        $this->messageManager = $messageManager;
    }

    /**
     * Sale Order Invoice After event handler
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $route = $this->_requestInterface->getControllerName();
        $invoice = $observer->getEvent()->getInvoice();
        $orderId = $invoice->getOrderId();
        $invoiceNumber = $invoice->getIncrementId();
        $helper = $this->_connection;
        $autoInvoice = $helper->getStoreConfig('odoomagentoconnect/order_settings/invoice_order');
        $showMessages = $helper->getStoreConfig('odoomagentoconnect/additional/show_messages');
        if ($autoInvoice == 1 && $orderId && in_array($route, ['order', 'order_shipment', 'order_invoice', 'manual', 'order_create', 'express', 'checkout'])) {
            $mappingcollection = $this->_orderMapping
                ->getCollection()
                ->addFieldToFilter('magento_id', ['eq'=>$orderId]);
            
            if ($mappingcollection->getSize() > 0) {
                $thisOrder = $this->_orderModel->load($orderId);
                $helper->getSocketConnect();
                $userId = $helper->getSession()->getUserId();
                if ($userId > 0) {
                    foreach ($mappingcollection as $map) {
                        $odooId = (int)$map->getOdooId();
                        if ($odooId > 0) {
                            $partnerId = $map->getOdooCustomerId();
                            $odooName = $map->getOdooOrder();
                            $status = $this->_orderMapResource->checkOdooOrderStatus($odooId, 'is_invoiced');

                            if (!$status) {
                                $inv = $this->_orderMapResource
                                    ->invoiceOdooOrder($thisOrder, $odooId, $invoiceNumber);
                                if ($showMessages) {
                                    if (!$inv) {
                                        $message = "Odoo Order ".$odooName." Not Invoiced, check Log for more details!!!";
                                        //$this->messageManager->addError(__($message));
                                    } else {
                                        $message = "Odoo Order ".$odooName." Successfully Invoiced.";
                                        //$this->messageManager->addSuccess(__($message));
                                    }
                                }
                            } elseif ($status == true && $showMessages) {
                                $message = "Odoo Order ".$odooName." is Already Invoiced.";
                                //$this->messageManager->addNotice(__($message));
                            } elseif ($showMessages) {
                                $message = "Odoo Order ".$odooName." Not Invoiced, check Log for more details!!!";
                                //$this->messageManager->addError(__($message));
                            }
                        }
                    }
                }
            }
        }
    }
}
