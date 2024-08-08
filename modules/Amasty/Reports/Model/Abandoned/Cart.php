<?php

namespace Amasty\Reports\Model\Abandoned;

use Amasty\Reports\Model\ResourceModel\Abandoned\Cart as ResourceCart;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Cart
 * @package Amasty\Reports\Model\Abandoned
 */
class Cart extends AbstractModel
{
    public function _construct()
    {
        $this->_init(ResourceCart::class);
    }

    /**
     * @param int $quoteId
     */
    public function loadByQuoteId($quoteId)
    {
        $this->getResource()->load($this, $quoteId, ResourceCart::QUOTE_ID);
    }
}
