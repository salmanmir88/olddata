<?php
/**
 * Copyright © InvoiceStatusColumn All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\InvoiceStatusColumn\Observer\Sales;
use Magento\Framework\App\ResourceConnection;
class OrderSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Order save after constructor.
     */
    protected $OrdergridFactory;

    public function __construct(
        ResourceConnection $resourceConnection,
        \Dakha\InvoiceStatusColumn\Model\OrdergridFactory $OrdergridFactory
    )
    {
        $this->resourceConnection = $resourceConnection;
        $this->OrdergridFactory   = $OrdergridFactory;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $order = $observer->getEvent()->getOrder();
        if ($order instanceof \Magento\Framework\Model\AbstractModel) {
            if ($order->getInvoiceCollection()->count()>0) {
                $order->setInvoiceStatus(1);
                $order->save();
                $orderGridModel = $this->OrdergridFactory->create()->load($order->getId());
                $orderGridModel->setInvoiceStatus(1);
                $orderGridModel->save();

            }elseif ($order->getInvoiceCollection()->count()<1) {
                $order->setInvoiceStatus(2);
                $order->save();
                $orderGridModel = $this->OrdergridFactory->create()->load($order->getId());
                $orderGridModel->setInvoiceStatus(2);
                $orderGridModel->save();
            }
            if($order->getCouponCode())
            {   
                $connection = $this->resourceConnection->getConnection();
                $data = ["coupon_code"=>$order->getCouponCode()]; 
                $where = ['entity_id = ?' => $order->getId()];
                $tableName = $this->resourceConnection->getTableName('sales_order_grid');
                $connection->update($tableName, $data, $where);
            }
        }
    }
}

