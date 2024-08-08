<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Affiliate for Magento 2
*/

namespace Amasty\Affiliate\Model\Source;

class OrderStatus implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory
     */
    protected $statusCollectionFactory;

    /**
     * OrderStatus constructor.
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory
    ) {
        $this->statusCollectionFactory = $statusCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return $options = $this->statusCollectionFactory->create()->toOptionArray();
    }
}
