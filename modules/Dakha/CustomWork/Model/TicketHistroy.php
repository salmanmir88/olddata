<?php
/**
 * Copyright © CustomWork All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\CustomWork\Model;

use Dakha\CustomWork\Api\Data\TicketHistroyInterface;
use Magento\Framework\Model\AbstractModel;

class TicketHistroy extends AbstractModel implements TicketHistroyInterface
{

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Dakha\CustomWork\Model\ResourceModel\TicketHistroy::class);
    }

    /**
     * @inheritDoc
     */
    public function getTickethistroyId()
    {
        return $this->getData(self::TICKETHISTROY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setTickethistroyId($tickethistroyId)
    {
        return $this->setData(self::TICKETHISTROY_ID, $tickethistroyId);
    }

    /**
     * @inheritDoc
     */
    public function getTicketId()
    {
        return $this->getData(self::TICKET_ID);
    }

    /**
     * @inheritDoc
     */
    public function setTicketId($ticketId)
    {
        return $this->setData(self::TICKET_ID, $ticketId);
    }

    /**
     * @inheritDoc
     */
    public function getTicketHistroy()
    {
        return $this->getData(self::TICKET_HISTROY);
    }

    /**
     * @inheritDoc
     */
    public function setTicketHistroy($ticketHistroy)
    {
        return $this->setData(self::TICKET_HISTROY, $ticketHistroy);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}

