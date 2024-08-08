<?php
/**
 * Copyright © Dakha All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\UserRolePermission\Model;

use Dakha\UserRolePermission\Api\Data\UserrolesInterface;
use Magento\Framework\Model\AbstractModel;

class Userroles extends AbstractModel implements UserrolesInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Dakha\UserRolePermission\Model\ResourceModel\Userroles::class);
    }

    /**
     * @inheritDoc
     */
    public function getUserrolesId()
    {
        return $this->getData(self::USERROLES_ID);
    }

    /**
     * @inheritDoc
     */
    public function setUserrolesId($userrolesId)
    {
        return $this->setData(self::USERROLES_ID, $userrolesId);
    }

    /**
     * @inheritDoc
     */
    public function getAllowed()
    {
        return $this->getData(self::ALLOWED);
    }

    /**
     * @inheritDoc
     */
    public function setAllowed($allowed)
    {
        return $this->setData(self::ALLOWED, $allowed);
    }

    /**
     * @inheritDoc
     */
    public function getUserId()
    {
        return $this->getData(self::USER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setUserId($userId)
    {
        return $this->setData(self::USER_ID, $userId);
    }
}

