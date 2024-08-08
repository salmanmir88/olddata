<?php
/**
 * Copyright © CustomWork All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\CustomWork\Model\ResourceModel\TicketHistroy;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Dakha\CustomWork\Model\TicketHistroy::class,
            \Dakha\CustomWork\Model\ResourceModel\TicketHistroy::class
        );
    }
}

