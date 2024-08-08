<?php
namespace Dakha\InvoiceStatusColumn\Model\ResourceModel;

class Ordergrid extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('sales_order_grid', 'entity_id');
    }
}
?>