<?php

namespace MyFatoorah\MyFatoorahPaymentGatewayDummy\Model\ResourceModel\MyfatoorahInvoice;

use MyFatoorah\MyFatoorahPaymentGatewayDummy\Model\MyfatoorahInvoice as MyfatoorahInvoiceModel;
use MyFatoorah\MyFatoorahPaymentGatewayDummy\Model\ResourceModel\MyfatoorahInvoice as MyfatoorahInvoiceResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection {

//    protected $_idFieldName = 'id';

    protected function _construct() {
        $this->_init(
                MyfatoorahInvoiceModel::class,
                MyfatoorahInvoiceResourceModel::class
        );
    }

}
