<?php
/**
 * Copyright © CustomWork All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\CustomWork\Api\Data;

interface TicketHistroySearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get TicketHistroy list.
     * @return \Dakha\CustomWork\Api\Data\TicketHistroyInterface[]
     */
    public function getItems();

    /**
     * Set ticket_id list.
     * @param \Dakha\CustomWork\Api\Data\TicketHistroyInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

