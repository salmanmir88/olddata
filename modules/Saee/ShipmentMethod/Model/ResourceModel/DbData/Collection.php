<?php
namespace Saee\ShipmentMethod\Model\ResourceModel\DbData;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Saee\ShipmentMethod\Model\DbData;
use Saee\ShipmentMethod\Model\ResourceModel\DbData as DbDataResourceModel;


/**
 * Class Collection
 * @package Saee\ShipmentMethod\Model\ResourceModel\DbData
 */
class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(DbData::class, DbDataResourceModel::class);
    }

}

