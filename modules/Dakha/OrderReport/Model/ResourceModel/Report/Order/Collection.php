<?php

namespace Dakha\OrderReport\Model\ResourceModel\Report\Order;

/**
 * Report order collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Collection extends \Magento\Sales\Model\ResourceModel\Report\Collection\AbstractCollection
{
    /**
     * Period format
     *
     * @var string
     */
    protected $_periodFormat;

    /**
     * Aggregated Data Table
     *
     * @var string
     */
    //protected $_aggregationTable = 'sales_order_aggregated_created';
    protected $_aggregationTable = 'sales_order';

    /**
     * Selected columns
     *
     * @var array
     */
    protected $_selectedColumns = [];

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Sales\Model\ResourceModel\Report $resource
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Sales\Model\ResourceModel\Report $resource,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        $resource->init($this->_aggregationTable);
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $resource, $connection);
    }

    /**
     * Get selected columns
     *
     * @return array
     */
    protected function _getSelectedColumns()
    {
        $connection = $this->getConnection();
        if ('month' == $this->_period) {
            $this->_periodFormat = $connection->getDateFormatSql('period', '%Y-%m');
        } elseif ('year' == $this->_period) {
            $this->_periodFormat = $connection->getDateExtractSql(
                'period',
                \Magento\Framework\DB\Adapter\AdapterInterface::INTERVAL_YEAR
            );
        } else {
            $this->_periodFormat = $connection->getDateFormatSql('period', '%Y-%m-%d');
        }
            $this->_selectedColumns = [
                'increment_id' => 'increment_id',
                'creation_date' => 'created_at',
                'shipping_amount' => 'shipping_amount',
                'subtotal' => 'subtotal',
                'grand_total' => 'grand_total',

            ];
        $this->getSelect()->joinLeft(
            ['orderaddress' => $this->getTable('sales_order_address')],
            'orderaddress.parent_id ='.$this->_aggregationTable. '.entity_id',
            ['country_id','customer_name'=> "CONCAT(firstname,' ',lastname)"]);

        $this->getSelect()->joinLeft(
            ['orderitems' => $this->getTable('sales_order_item')],
            'orderitems.order_id ='.$this->_aggregationTable. '.entity_id',
            ['sku','name','qty_ordered']);

        return $this->_selectedColumns;
    }

    /**
     * Apply custom columns before load
     *
     * @return $this
     */
    protected function _beforeLoad()
    {
        $this->getSelect()->from($this->getResource()->getMainTable(), $this->_getSelectedColumns());

        return parent::_beforeLoad();
    }

    /**
     * Apply date range filter
     *
     * @return $this
     */
    protected function _applyDateRangeFilter()
    {
        // Remember that field PERIOD is a DATE(YYYY-MM-DD) in all databases
        if ($this->_from !== null) {
            $this->getSelect()->where($this->_aggregationTable.'.created_at >= ?', $this->_from.' 00:00:00');
        }
        if ($this->_to !== null) {
            $this->getSelect()->where($this->_aggregationTable.'.created_at <= ?', $this->_to.' 23:23:59');
        }

        return $this;
    }

    /**
     * Apply stores filter to select object Also apply custom shipping address condition
     *
     * @param \Magento\Framework\DB\Select $select
     * @return $this
     */
    protected function _applyStoresFilterToSelect(\Magento\Framework\DB\Select $select)
    {
        $nullCheck = false;
        $storeIds = $this->_storesIds;

        if (!is_array($storeIds)) {
            $storeIds = [$storeIds];
        }

        $storeIds = array_unique($storeIds);

        if ($index = array_search(null, $storeIds)) {
            unset($storeIds[$index]);
            $nullCheck = true;
        }

        if ($nullCheck) {
            $select->where($this->_aggregationTable.'.store_id IN(?) OR store_id IS NULL', $storeIds);
        } else {
            $select->where($this->_aggregationTable.'.store_id IN(?)', $storeIds);
        }

        $select->where('orderaddress.address_type = \'shipping\'');
        //echo $this->getSelect();exit;
        return $this;
    }
}