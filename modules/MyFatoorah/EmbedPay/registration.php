<?php

if (!defined('MYFATOORAH_LOG_FILE')) {
    define('MYFATOORAH_LOG_FILE', BP . '/var/log/myfatoorah.log');
}

\Magento\Framework\Component\ComponentRegistrar::register(
        \Magento\Framework\Component\ComponentRegistrar::MODULE,
        'MyFatoorah_EmbedPay',
        __DIR__
);
