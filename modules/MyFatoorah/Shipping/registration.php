<?php

if (!defined('MFSHIPPING_LOG_FILE')) {
    define('MFSHIPPING_LOG_FILE', BP . '/var/log/myfatoorah_shipping.log');
}

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'MyFatoorah_Shipping',
    __DIR__
);
