<?php
/**
 * Copyright © InvoiceStatusColumn All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\InvoiceStatusColumn\Observer\Sales;
use Magento\Framework\App\ResourceConnection;

class OrderInvoicePay implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * Invoice pay constructor.
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
        try {
            $invoice = $observer->getEvent()->getInvoice();
            $order = $invoice->getOrder();
            $order->setInvoiceStatus(1);
            $order->save();
            $orderGridModel = $this->OrdergridFactory->create()->load($order->getId());
            $orderGridModel->setInvoiceStatus(1);
            $orderGridModel->save();
        } catch (\Exception $e) {
            
        }
    }
}

