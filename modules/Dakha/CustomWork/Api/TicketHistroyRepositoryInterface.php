<?php
/**
 * Copyright © CustomWork All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\CustomWork\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface TicketHistroyRepositoryInterface
{

    /**
     * Save TicketHistroy
     * @param \Dakha\CustomWork\Api\Data\TicketHistroyInterface $ticketHistroy
     * @return \Dakha\CustomWork\Api\Data\TicketHistroyInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Dakha\CustomWork\Api\Data\TicketHistroyInterface $ticketHistroy
    );

    /**
     * Retrieve TicketHistroy
     * @param string $tickethistroyId
     * @return \Dakha\CustomWork\Api\Data\TicketHistroyInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($tickethistroyId);

    /**
     * Retrieve TicketHistroy matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Dakha\CustomWork\Api\Data\TicketHistroySearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete TicketHistroy
     * @param \Dakha\CustomWork\Api\Data\TicketHistroyInterface $ticketHistroy
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Dakha\CustomWork\Api\Data\TicketHistroyInterface $ticketHistroy
    );

    /**
     * Delete TicketHistroy by ID
     * @param string $tickethistroyId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($tickethistroyId);
}

