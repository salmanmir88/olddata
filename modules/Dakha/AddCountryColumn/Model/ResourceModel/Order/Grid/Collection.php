<?php declare(strict_types=1);

namespace Dakha\AddCountryColumn\Model\ResourceModel\Order\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as OrderGridCollection;
use Zend_Db_Expr;

/**
 * Class Collection
 * @package MarkShust\OrderGrid\Model\ResourceModel\Order\Grid
 */
class Collection extends OrderGridCollection
{
    /**
     * Initialize the select statement.
     *
     * @return $this
     */
    protected function _initSelect(): self
    {
        $this->addFilterToMap('created_at', 'main_table.created_at')->addFilterToMap('status', 'main_table.status')->addFilterToMap('store_id', 'main_table.store_id');
        parent::_initSelect();

        // Add the sales_order_item model to this collection
        $this->join(
            [$this->getTable('sales_order_address')],
            "main_table.entity_id = {$this->getTable('sales_order_address')}.parent_id",
            []
        );

        // Group by the order id, which is initially what this grid is id'd by
        $this->getSelect()->group('main_table.entity_id');

        return $this;
    }

    /**
     * Add field to filter.
     *
     * @param string|array $field
     * @param string|int|array|null $condition
     * @return SearchResult
     */
    public function addFieldToFilter($field, $condition = null): SearchResult
    {
        if ($field === 'country_id' || $field === 'city' || $field === 'telephone' || $field === 'postcode' || $field === 'street' && !$this->getFlag('country_filter_added')) {
            $orderAddressTableName = 'sales_order_address';
            $this->getSelect()->group('main_table.entity_id');
            $this->getSelect()->getSelect()->joinLeft(
                    ['soa' => $orderAddressTableName],
                    'soa.parent_id = main_table.entity_id AND soa.address_type = \'shipping\'',
                    ['soa.country_id','soa.telephone','soa.city','soa.postcode','soa.street']
                );

            $this->setFlag('country_filter_added', 1);

            return $this;
        } else {
            return parent::addFieldToFilter($field, $condition);
        }

    }

}