<?php

namespace Dakha\OrderReport\Model\ResourceModel\Salesitem;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Dakha\OrderReport\Model\Salesitem', 'Dakha\OrderReport\Model\ResourceModel\Salesitem');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>