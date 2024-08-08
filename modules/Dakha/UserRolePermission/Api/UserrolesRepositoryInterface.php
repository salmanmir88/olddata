<?php
/**
 * Copyright © Dakha All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\UserRolePermission\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface UserrolesRepositoryInterface
{

    /**
     * Save userroles
     * @param \Dakha\UserRolePermission\Api\Data\UserrolesInterface $userroles
     * @return \Dakha\UserRolePermission\Api\Data\UserrolesInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Dakha\UserRolePermission\Api\Data\UserrolesInterface $userroles
    );

    /**
     * Retrieve userroles
     * @param string $userrolesId
     * @return \Dakha\UserRolePermission\Api\Data\UserrolesInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($userrolesId);

    /**
     * Retrieve userroles matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Dakha\UserRolePermission\Api\Data\UserrolesSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete userroles
     * @param \Dakha\UserRolePermission\Api\Data\UserrolesInterface $userroles
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Dakha\UserRolePermission\Api\Data\UserrolesInterface $userroles
    );

    /**
     * Delete userroles by ID
     * @param string $userrolesId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($userrolesId);
}

