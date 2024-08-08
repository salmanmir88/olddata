<?php
/**
 * Copyright © Dakha All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\UserRolePermission\Api\Data;

interface UserrolesSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get userroles list.
     * @return \Dakha\UserRolePermission\Api\Data\UserrolesInterface[]
     */
    public function getItems();

    /**
     * Set allowed list.
     * @param \Dakha\UserRolePermission\Api\Data\UserrolesInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

