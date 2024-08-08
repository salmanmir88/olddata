<?php
namespace Aalogics\SearchByTelephone\Model\Plugin\Sales\Order;

class Grid
{

	public static $table = 'sales_order_grid';
	public static $leftJoinTable = 'sales_order_address';

	public function afterSearch($intercepter, $collection)
	{
		if ($collection->getMainTable() === $collection->getConnection()->getTableName(self::$table)) {

			$leftJoinTableName = $collection->getConnection()->getTableName(self::$leftJoinTable);

			$collection
			->getSelect()
			->joinLeft(
					['coo'=>$leftJoinTableName],
					"coo.parent_id = main_table.entity_id AND coo.address_type = 'billing'",
					[
					'telehone' => 'coo.telephone'
					]
			);
			
			$where = $collection->getSelect()->getPart(\Magento\Framework\DB\Select::WHERE);

			$collection->getSelect()->setPart(\Magento\Framework\DB\Select::WHERE, $where);

			//echo $collection->getSelect()->__toString();die;


		}
		return $collection;


	}


}