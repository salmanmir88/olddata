<?php
/**
 * Copyright © Dakha All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\UserRolePermission\Model\ResourceModel\Userroles;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'userroles_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Dakha\UserRolePermission\Model\Userroles::class,
            \Dakha\UserRolePermission\Model\ResourceModel\Userroles::class
        );
    }
}

