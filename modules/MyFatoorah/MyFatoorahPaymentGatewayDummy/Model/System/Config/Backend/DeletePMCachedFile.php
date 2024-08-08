<?php

namespace MyFatoorah\MyFatoorahPaymentGatewayDummy\Model\System\Config\Backend;

use Magento\Framework\App\Config\Value;
use MyFatoorah\Library\PaymentMyfatoorahApiV2D;

class DeletePMCachedFile extends Value {

//---------------------------------------------------------------------------------------------------------------------------------------------------
    public function beforeSave() {
        if (file_exists(PaymentMyfatoorahApiV2D::$pmCachedFile)) {
            unlink(PaymentMyfatoorahApiV2D::$pmCachedFile);
        }
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
}
