<?php
/**
 * Copyright © Dakha All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\UserRolePermission\Model;

use Dakha\UserRolePermission\Api\Data\UserrolesInterface;
use Dakha\UserRolePermission\Api\Data\UserrolesInterfaceFactory;
use Dakha\UserRolePermission\Api\Data\UserrolesSearchResultsInterfaceFactory;
use Dakha\UserRolePermission\Api\UserrolesRepositoryInterface;
use Dakha\UserRolePermission\Model\ResourceModel\Userroles as ResourceUserroles;
use Dakha\UserRolePermission\Model\ResourceModel\Userroles\CollectionFactory as UserrolesCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class UserrolesRepository implements UserrolesRepositoryInterface
{

    /**
     * @var Userroles
     */
    protected $searchResultsFactory;

    /**
     * @var UserrolesCollectionFactory
     */
    protected $userrolesCollectionFactory;

    /**
     * @var ResourceUserroles
     */
    protected $resource;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var UserrolesInterfaceFactory
     */
    protected $userrolesFactory;


    /**
     * @param ResourceUserroles $resource
     * @param UserrolesInterfaceFactory $userrolesFactory
     * @param UserrolesCollectionFactory $userrolesCollectionFactory
     * @param UserrolesSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceUserroles $resource,
        UserrolesInterfaceFactory $userrolesFactory,
        UserrolesCollectionFactory $userrolesCollectionFactory,
        UserrolesSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->userrolesFactory = $userrolesFactory;
        $this->userrolesCollectionFactory = $userrolesCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(UserrolesInterface $userroles)
    {
        try {
            $this->resource->save($userroles);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the userroles: %1',
                $exception->getMessage()
            ));
        }
        return $userroles;
    }

    /**
     * @inheritDoc
     */
    public function get($userrolesId)
    {
        $userroles = $this->userrolesFactory->create();
        $this->resource->load($userroles, $userrolesId);
        if (!$userroles->getId()) {
            throw new NoSuchEntityException(__('userroles with id "%1" does not exist.', $userrolesId));
        }
        return $userroles;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->userrolesCollectionFactory->create();
        
        $this->collectionProcessor->process($criteria, $collection);
        
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        
        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }
        
        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(UserrolesInterface $userroles)
    {
        try {
            $userrolesModel = $this->userrolesFactory->create();
            $this->resource->load($userrolesModel, $userroles->getUserrolesId());
            $this->resource->delete($userrolesModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the userroles: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($userrolesId)
    {
        return $this->delete($this->get($userrolesId));
    }
}

