<?php

namespace Amasty\Shopby\Plugin\CatalogSearch\Model\Adapter\Mysql\Filter;

use Magento\Framework\Search\Request\FilterInterface;
use Magento\CatalogSearch\Model\Search\RequestGenerator;
use Magento\Framework\App\ResourceConnection;
use Amasty\Shopby\Helper\Category;
use Magento\Framework\Module\Manager;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Preprocessor
{
    private $validFields = ['rating_summary'];

    private $invalidFields = ['am_on_sale', 'am_is_new'];

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(
        ResourceConnection $resource,
        Manager $moduleManager
    ) {
        $this->connection = $resource->getConnection();
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param FilterInterface $filter
     * @param $isNegation
     * @param $query
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormatParameter)
     */
    public function aroundProcess(
        $subject,
        callable $proceed,
        FilterInterface $filter,
        $isNegation,
        $query
    ) {
        if (in_array($filter->getField(), $this->invalidFields)) {
            return '';
        }
        if (in_array($filter->getField(), $this->validFields)) {
            $alias = $filter->getField() . RequestGenerator::FILTER_SUFFIX;
            return str_replace(
                $this->connection->quoteIdentifier($filter->getField()),
                $this->connection->quoteIdentifier($alias . '.' . $filter->getField()),
                $query
            );
        } elseif ($filter->getField() === Category::STORE_CODE) {
            return $this->resolveAlias($query, 'store_id', 'store_id');
        } elseif ($filter->getField() === Category::ATTRIBUTE_CODE && is_array($filter->getValue())) {
            return $this->resolveAlias($query, 'category_ids', 'category_id');
        }

        return $proceed($filter, $isNegation, $query);
    }

    /**
     * @param string $query
     * @param string $column1
     * @param string $column2
     * @return string
     */
    private function resolveAlias($query, $column1, $column2)
    {
        $alias = 'category_ids_index';

        return str_replace(
            $this->connection->quoteIdentifier($column1),
            $this->connection->quoteIdentifier($alias . '.' . $column2),
            $query
        );
    }
}
