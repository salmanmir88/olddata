<?php

namespace MyFatoorah\MyFatoorahPaymentGateway\Model\System\Config\Backend;

use Magento\Framework\App\Config\Value;
use MyFatoorah\Library\PaymentMyfatoorahApiV2;

class DeletePMCachedFile extends Value {

//---------------------------------------------------------------------------------------------------------------------------------------------------
    public function beforeSave() {
        if (file_exists(PaymentMyfatoorahApiV2::$pmCachedFile)) {
            unlink(PaymentMyfatoorahApiV2::$pmCachedFile);
        }
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
}
