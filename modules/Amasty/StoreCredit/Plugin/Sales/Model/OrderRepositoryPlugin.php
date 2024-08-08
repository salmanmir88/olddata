<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StoreCredit
 */


namespace Amasty\StoreCredit\Plugin\Sales\Model;

use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Sales\Model\OrderRepository as MagentoOrderRepository;

class OrderRepositoryPlugin
{
    /**
     * @var array
     */
    private $listAttributes = [
        'amstorecredit_base_amount',
        'amstorecredit_amount',
        'amstorecredit_invoiced_base_amount',
        'amstorecredit_invoiced_amount',
        'amstorecredit_refunded_base_amount',
        'amstorecredit_refunded_amount'
    ];

    /**
     * @var OrderExtensionFactory
     */
    private $orderExtensionFactory;

    public function __construct(
        OrderExtensionFactory $orderExtensionFactory
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
    }

    /**
     * @param MagentoOrderRepository $subject
     * @param OrderInterface $order
     *
     * @return OrderInterface
     */
    public function afterGet(MagentoOrderRepository $subject, OrderInterface $order)
    {
        $this->prepareStoreCreditExtensionAttributes($order);

        return $order;
    }

    /**
     * @param MagentoOrderRepository $subject
     * @param OrderSearchResultInterface $orderCollection
     *
     * @return OrderSearchResultInterface
     */
    public function afterGetList(MagentoOrderRepository $subject, OrderSearchResultInterface $orderCollection)
    {
        foreach ($orderCollection->getItems() as $order) {
            $this->prepareStoreCreditExtensionAttributes($order);
        }

        return $orderCollection;
    }

    /**
     * @param OrderInterface $order
     */
    private function prepareStoreCreditExtensionAttributes(OrderInterface $order)
    {
        $extensionAttributes = $order->getExtensionAttributes();

        if ($extensionAttributes === null) {
            $extensionAttributes = $this->orderExtensionFactory->create();
        }

        foreach ($this->listAttributes as $attributeName) {
            $extensionAttributes->setData($attributeName, $order->getData($attributeName));
            $order->setExtensionAttributes($extensionAttributes);
        }
    }
}
