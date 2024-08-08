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
		$categoryValue	= $blockInstance->getCategoryValue();
		
		
		
		// $logger->info( __FILE__ . " :: ".__LINE__); 

        $connection = $this->getConnection();
        $orderTableAliasName = $connection->quoteIdentifier('order');

		/** for filter by order status **/
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
                'order_items_sku' => 'order_items.sku',
                'order_items_barcode' => 'order_items.barcode',
                'order_items_link' => 'order_items.link',
                'order_items_product_model' => 'order_items.product_model',
                'order_items_version' => 'order_items.version',
                'order_items_album_qyt' => 'order_items.album_qyt',
                'order_items_is_featured' => 'order_items.is_featured',
                'order_items_upc' => 'order_items.upc',
                'order_items_link' => 'order_items.link',
            ]
        );
		
		/** Join b/w two table "sales_order_item" and "catalog_category_product" for filter by category id **/
		if($categoryValue > 0){
			$this->getSelect()->joinLeft(
				   array('category_product' => 'catalog_category_product'),
				   'order_items.product_id = category_product.product_id',
				   array('category_id' => 'category_id')
			);
		}
		
		$this->getSelect()->joinInner(
            ['order' => $this->getTable('sales_order')],
            implode(' AND ', $orderJoinCondition),
            []
        );
		
		if($categoryValue > 0){
			/** Ordered product report filter by category id **/
			$this->getSelect()->where(
				// 'order_items.parent_item_id IS NULL'
				'order_items.parent_item_id IS NULL AND category_id ='.$categoryValue
			);
		}else{
			$this->getSelect()->where(
				'order_items.parent_item_id IS NULL'
			);
		}
		
		$this->getSelect()->having(
            'order_items.qty_ordered > ?',
            0
        )->columns(
            'SUM(order_items.qty_ordered) as ordered_qty'
        )->group(
            'order_items.product_id'
        );
		
		
		// $logger->info("report_filter Query -- ". __FILE__ . " :: ".__LINE__); 
		// $logger->info($this->getSelect());
		
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
