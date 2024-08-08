<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Affiliate for Magento 2
*/

namespace Amasty\Affiliate\Api;

/**
 * Interface LifetimeRepositoryInterface
 * @api
 */
interface LifetimeRepositoryInterface
{
    /**
     * @param \Amasty\Affiliate\Api\Data\LifetimeInterface $account
     * @return \Amasty\Affiliate\Api\Data\LifetimeInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\Affiliate\Api\Data\LifetimeInterface $account);

    /**
     * @param int $accountId
     * @return \Amasty\Affiliate\Api\Data\LifetimeInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($accountId);

    /**
     * @param \Amasty\Affiliate\Api\Data\LifetimeInterface $account
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Affiliate\Api\Data\LifetimeInterface $account);

    /**
     * @param int $accountId
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($accountId);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Eav\Api\Data\AttributeSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
