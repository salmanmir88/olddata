<?php
namespace Dakha\InvoiceStatusColumn\Model;

class Ordergrid extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Dakha\InvoiceStatusColumn\Model\ResourceModel\Ordergrid');
    }
}
?>