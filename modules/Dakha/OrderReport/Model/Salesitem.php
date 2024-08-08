<?php
namespace Dakha\OrderReport\Model;

class Salesitem extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Dakha\OrderReport\Model\ResourceModel\Salesitem');
    }
}
?>