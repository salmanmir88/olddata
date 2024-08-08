<?php
/**
 * Copyright © CustomWork All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\CustomWork\Api\Data;

interface TicketHistroyInterface
{

    const TICKET_HISTROY = 'ticket_histroy';
    const UPDATED_AT = 'updated_at';
    const TICKETHISTROY_ID = 'tickethistroy_id';
    const TICKET_ID = 'ticket_id';
    const CREATED_AT = 'created_at';

    /**
     * Get tickethistroy_id
     * @return string|null
     */
    public function getTickethistroyId();

    /**
     * Set tickethistroy_id
     * @param string $tickethistroyId
     * @return \Dakha\CustomWork\TicketHistroy\Api\Data\TicketHistroyInterface
     */
    public function setTickethistroyId($tickethistroyId);

    /**
     * Get ticket_id
     * @return string|null
     */
    public function getTicketId();

    /**
     * Set ticket_id
     * @param string $ticketId
     * @return \Dakha\CustomWork\TicketHistroy\Api\Data\TicketHistroyInterface
     */
    public function setTicketId($ticketId);

    /**
     * Get ticket_histroy
     * @return string|null
     */
    public function getTicketHistroy();

    /**
     * Set ticket_histroy
     * @param string $ticketHistroy
     * @return \Dakha\CustomWork\TicketHistroy\Api\Data\TicketHistroyInterface
     */
    public function setTicketHistroy($ticketHistroy);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return \Dakha\CustomWork\TicketHistroy\Api\Data\TicketHistroyInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return \Dakha\CustomWork\TicketHistroy\Api\Data\TicketHistroyInterface
     */
    public function setUpdatedAt($updatedAt);
}

