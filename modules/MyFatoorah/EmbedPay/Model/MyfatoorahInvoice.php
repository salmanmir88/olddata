<?php

namespace MyFatoorah\EmbedPay\Model;

class MyfatoorahInvoice extends \Magento\Framework\Model\AbstractModel {

    public function _construct() {
        $this->_init('MyFatoorah\EmbedPay\Model\ResourceModel\MyfatoorahInvoice');
    }

}
