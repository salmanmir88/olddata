<?php
namespace Dakha\OrderReport\Model\ResourceModel;

class Salesitem extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_order_item', 'item_id');
    }
}
?>