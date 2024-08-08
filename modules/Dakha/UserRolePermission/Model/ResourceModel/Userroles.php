<?php
/**
 * Copyright © Dakha All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\UserRolePermission\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Userroles extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('dakha_userrolepermission_userroles', 'userroles_id');
    }
}

