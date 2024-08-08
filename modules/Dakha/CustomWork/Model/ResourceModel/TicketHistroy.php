<?php
/**
 * Copyright © CustomWork All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\CustomWork\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class TicketHistroy extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('customwork_tickethistroy', 'id');
    }
}

