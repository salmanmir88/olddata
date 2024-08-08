<?php

namespace Meetanshi\GoogleSitemap\Model\Config\Source;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Option\ArrayInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CmsPages
 * @package Meetanshi\GoogleSitemap\Model\Config\Source
 */
class CmsPages implements ArrayInterface
{
    /**
     * @var PageRepositoryInterface
     */
    private $pageRepositoryInterface;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * CmsPages constructor.
     * @param PageRepositoryInterface $pageRepositoryInterface
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        PageRepositoryInterface $pageRepositoryInterface,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->pageRepositoryInterface = $pageRepositoryInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return \Exception|PageInterface[]|LocalizedException
     */
    public function getCmsPageCollection()
    {
        $searchCriteria = $searchCriteria = $this->searchCriteriaBuilder->create();
        try {
            $collection = $this->pageRepositoryInterface->getList($searchCriteria)->getItems();
        } catch (LocalizedException $e) {
            return $e;
        }
        return $collection;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        try {
            $pages = $this->getCmsPageCollection();
            if ($pages instanceof LocalizedException) {
                throw $pages;
            }
            $cnt = 0;
            foreach ($pages as $page) {
                $optionArray[$cnt]['value'] = $page->getIdentifier();
                $optionArray[$cnt++]['label'] = $page->getTitle();
            }
        } catch (LocalizedException $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->info($e->getMessage());
        } catch (\Exception $e) {
            ObjectManager::getInstance()->get(LoggerInterface::class)->info($e->getMessage());
        }
        return $optionArray;
    }
}
