<?php

namespace Dakha\InvoiceStatusColumn\Model\ResourceModel\Ordergrid;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Dakha\InvoiceStatusColumn\Model\Ordergrid', 'Dakha\InvoiceStatusColumn\Model\ResourceModel\Ordergrid');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>