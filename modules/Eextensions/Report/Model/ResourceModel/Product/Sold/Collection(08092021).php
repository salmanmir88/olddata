<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Report Sold Products collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Eextensions\Report\Model\ResourceModel\Product\Sold;

use Magento\Framework\DB\Select;

/**
 * Data collection.
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Reports\Model\ResourceModel\Product\Sold\Collection
{
    /**
     * Set Date range to collection.
     *
     * @param int $from
     * @param int $to
     * @return $this
     */
    public function setDateRange($from, $to)
    {
        $this->_reset()->addAttributeToSelect(
            '*'
        )->addOrderedQty(
            $from,
            $to
        )->setOrder(
            'ordered_qty',
            self::SORT_ORDER_DESC
        );
        return $this;
    }

    /**
     * Add ordered qty's
     *
     * @param string $from
     * @param string $to
     * @return $this
     */
    public function addOrderedQty($from = '', $to = '')
    {
		$objectManager	= \Magento\Framework\App\ObjectManager::getInstance();  
		$blockInstance	= $objectManager->get('Magento\Reports\Block\Adminhtml\Grid');
		$statusValue	= $blockInstance->getStatusValue();
		
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/report_filter.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);
		
		/* foreach($this->getFilter() as $key=>$value){
			print_r($value);
		} */
		
		/* echo "<pre>";
		// print_r($_REQUEST);
		print_r(get_class_methods($requestENV));
		print_r($requestENV->getParams());
		print_r($requestENV->getPostValue());
		
		die; */
		
		$logger->info("Overrided Model"); 
		$logger->info("status filter value -- ". __FILE__ . " :: ".__LINE__); 
		$logger->info($statusValue);
		

        $connection = $this->getConnection();
        $orderTableAliasName = $connection->quoteIdentifier('order');

        if($statusValue == "0" || $statusValue == "canceled"){
			$orderJoinCondition = [
				$orderTableAliasName . '.entity_id = order_items.order_id',
				$connection->quoteInto("{$orderTableAliasName}.state <> ?", \Magento\Sales\Model\Order::STATE_CANCELED),
			];
		}else{
			$orderJoinCondition = [
				$orderTableAliasName . '.entity_id = order_items.order_id',
				$connection->quoteInto("{$orderTableAliasName}.state = ?", $statusValue),
			];
		}

        if ($from != '' && $to != '') {
            $fieldName = $orderTableAliasName . '.created_at';
            $orderJoinCondition[] = $this->prepareBetweenSql($fieldName, $from, $to);
        }

        $this->getSelect()->reset()->from(
            ['order_items' => $this->getTable('sales_order_item')],
            [
                'ordered_qty' => 'order_items.qty_ordered',
                'order_items_name' => 'order_items.name',
                'order_items_sku' => 'order_items.sku'
            ]
        )->joinLeft(
               array('second' => 'catalog_product_entity_varchar'),
               'order_items.product_id = second.entity_id and attribute_id = 156',
               array('barcode' => 'value')
        )->joinInner(
            ['order' => $this->getTable('sales_order')],
            implode(' AND ', $orderJoinCondition),
            []
        )->where(
            'order_items.parent_item_id IS NULL'
			// "order.state = 'complete'"
			// ("order_items.parent_item_id IS NULL") AND ("order.state = 'complete'")
        )->having(
            'order_items.qty_ordered > ?',
            0
        )->columns(
            'SUM(order_items.qty_ordered) as ordered_qty'
        )->group(
            'order_items.product_id'
        );
		
		
		$logger->info("report_filter Query -- ". __FILE__ . " :: ".__LINE__); 
		$logger->info($this->getSelect());
		
        return $this;
    }
	
	/**
     * Get filter by key
     *
     * @param string $name
     * @return string
     */
    /* public function getFilter($name)
    {
        return $this->getRequest()->getParam($name) ? $this->escapeHtml($this->getRequest()->getParam($name)) : '';
    } */

    /**
     * Set store filter to collection
     *
     * @param array $storeIds
     * @return $this
     */
    public function setStoreIds($storeIds)
    {
        if ($storeIds) {
            $this->getSelect()->where('order_items.store_id IN (?)', (array)$storeIds);
        }
        return $this;
    }

    /**
     * Set order
     *
     * @param string $attribute
     * @param string $dir
     * @return $this
     */
    public function setOrder($attribute, $dir = self::SORT_ORDER_DESC)
    {
        if (in_array($attribute, ['orders', 'ordered_qty'])) {
            $this->getSelect()->order($attribute . ' ' . $dir);
        } else {
            parent::setOrder($attribute, $dir);
        }

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return Select
     * @since 100.2.0
     */
    public function getSelectCountSql()
    {
        $countSelect = clone parent::getSelectCountSql();

        $countSelect->reset(Select::COLUMNS);
        $countSelect->columns('COUNT(DISTINCT order_items.item_id)');

        return $countSelect;
    }

    /**
     * Prepare between sql
     *
     * @param string $fieldName Field name with table suffix ('created_at' or 'main_table.created_at')
     * @param string $from
     * @param string $to
     * @return string Formatted sql string
     */
    protected function prepareBetweenSql($fieldName, $from, $to)
    {
        return sprintf(
            '(%s BETWEEN %s AND %s)',
            $fieldName,
            $this->getConnection()->quote($from),
            $this->getConnection()->quote($to)
        );
    }
}
