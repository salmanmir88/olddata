<?php

namespace MyFatoorah\MyFatoorahPaymentGateway\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface {

    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context) {
        $cards = [
            'kn'    => 'KNET',
            'vm'    => 'VISA/MASTER',
            'md'    => 'MADA',
            'b'     => 'Benefit',
            'np'    => 'Qatar Debit Cards',
            'uaecc' => 'UAE Debit Cards',
            's'     => 'Sadad',
            'ae'    => 'AMEX',
            'ap'    => 'Apple Pay',
            'kf'    => 'KFast',
            'af'    => 'AFS',
            'stc'   => 'STC Pay',
            'mz'    => 'Mezza',
            'oc'    => 'Orange Cash',
            'on'    => 'Oman Net',
            'M'     => 'Mpgs',
            'ccuae' => 'UAE DEBIT VISA',
            'vms'   => 'VISA/MASTER Saudi',
            'vmm'   => 'VISA/MASTER/MADA',
        ];
        if (version_compare('3.0.8', $context->getVersion()) >= 0) {
            $setup->startSetup();
            $conn      = $setup->getConnection();
            $tableName = $setup->getTable('myfatoorah_invoice');
            $setup->startSetup();

            foreach ($cards as $key => $value) {
                //Any Query
                $conn->query("UPDATE $tableName set gateway_name = '$value' where gateway_id='$key'");
            }

            $setup->endSetup();
        }
    }

}
