<?php
namespace Saee\ShipmentMethod\Model\Plugin\Sales\Order;

use Magento\Framework\DB\Select;

/**
 * Class Grid
 * @package Saee\ShipmentMethod\Model\Plugin\Sales\Order
 */
class Grid
{

    public static $table = 'sales_order_grid';
    public static $leftJoinTable = 'saee_response';

    /**
     * @param $intercepter
     * @param $collection
     * @return mixed
     */
    public function afterSearch($intercepter, $collection)
    {
        if ($collection->getMainTable() === $collection->getConnection()->getTableName(self::$table)) {

            $leftJoinTableName = $collection->getConnection()->getTableName(self::$leftJoinTable);

            $collection
                ->getSelect()
                ->joinLeft(
                    ['co'=>$leftJoinTableName],
                    "co.order_id = main_table.entity_id",
                    [
                        'waybill' => 'co.waybill'
                    ]
                )->group('main_table.entity_id');;

            $where = $collection->getSelect()->getPart(Select::WHERE);

            $collection->getSelect()->setPart(Select::WHERE, $where);
            //echo $collection->getSelect()->__toString();die;


        }

        return $collection;


    }


}
