<?php
/**
 * Copyright © CustomWork All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\CustomWork\Model;

use Dakha\CustomWork\Api\Data\TicketHistroyInterface;
use Dakha\CustomWork\Api\Data\TicketHistroyInterfaceFactory;
use Dakha\CustomWork\Api\Data\TicketHistroySearchResultsInterfaceFactory;
use Dakha\CustomWork\Api\TicketHistroyRepositoryInterface;
use Dakha\CustomWork\Model\ResourceModel\TicketHistroy as ResourceTicketHistroy;
use Dakha\CustomWork\Model\ResourceModel\TicketHistroy\CollectionFactory as TicketHistroyCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class TicketHistroyRepository implements TicketHistroyRepositoryInterface
{

    /**
     * @var ResourceTicketHistroy
     */
    protected $resource;

    /**
     * @var TicketHistroyInterfaceFactory
     */
    protected $ticketHistroyFactory;

    /**
     * @var TicketHistroy
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var TicketHistroyCollectionFactory
     */
    protected $ticketHistroyCollectionFactory;


    /**
     * @param ResourceTicketHistroy $resource
     * @param TicketHistroyInterfaceFactory $ticketHistroyFactory
     * @param TicketHistroyCollectionFactory $ticketHistroyCollectionFactory
     * @param TicketHistroySearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceTicketHistroy $resource,
        TicketHistroyInterfaceFactory $ticketHistroyFactory,
        TicketHistroyCollectionFactory $ticketHistroyCollectionFactory,
        TicketHistroySearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->ticketHistroyFactory = $ticketHistroyFactory;
        $this->ticketHistroyCollectionFactory = $ticketHistroyCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(TicketHistroyInterface $ticketHistroy)
    {
        try {
            $this->resource->save($ticketHistroy);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the ticketHistroy: %1',
                $exception->getMessage()
            ));
        }
        return $ticketHistroy;
    }

    /**
     * @inheritDoc
     */
    public function get($ticketHistroyId)
    {
        $ticketHistroy = $this->ticketHistroyFactory->create();
        $this->resource->load($ticketHistroy, $ticketHistroyId);
        if (!$ticketHistroy->getId()) {
            throw new NoSuchEntityException(__('TicketHistroy with id "%1" does not exist.', $ticketHistroyId));
        }
        return $ticketHistroy;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->ticketHistroyCollectionFactory->create();
        
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
    public function delete(TicketHistroyInterface $ticketHistroy)
    {
        try {
            $ticketHistroyModel = $this->ticketHistroyFactory->create();
            $this->resource->load($ticketHistroyModel, $ticketHistroy->getTickethistroyId());
            $this->resource->delete($ticketHistroyModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the TicketHistroy: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($ticketHistroyId)
    {
        return $this->delete($this->get($ticketHistroyId));
    }
}

