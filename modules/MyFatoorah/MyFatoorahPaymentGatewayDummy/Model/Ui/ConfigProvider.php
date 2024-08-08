<?php

namespace MyFatoorah\MyFatoorahPaymentGatewayDummy\Model\Ui;

use MyFatoorah\MyFatoorahPaymentGatewayDummy\Gateway\Config\Config;
use Magento\Checkout\Model\ConfigProviderInterface;

/**
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface {

    protected $_gatewayConfig;

    public function __construct(
            Config $gatewayConfig
    ) {
        $this->_gatewayConfig = $gatewayConfig;
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
    public function getConfig() {

        try {
            $data  = $this->listPaymentGateways();
            $error = null;
        } catch (\Exception $e) {
            $data = [];

            $error = $e->getMessage();
        }

        $config = [
            'payment' => [
                Config::CODE => [
                    'title'       => $this->_gatewayConfig->getTitle(),
                    'description' => $this->_gatewayConfig->getDescription(),
                    'listOptions' => $this->_gatewayConfig->getKeyGateways(),
                    'gateways'    => $data,
                    'mfError'     => $error
                ]
            ]
        ];

        return $config;
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
    public function listPaymentGateways() {

        if ($this->_gatewayConfig->getKeyGateways() == 'myfatoorah') {
            $gateways['myfatoorah'] = __('myfatoorah');
        } else {
            $mfGateways = $this->_gatewayConfig->getMyfatoorahObject()->getVendorGatewaysByType();

            $gateways = [];
            foreach ($mfGateways as $mfGateway) {
                $gateways[$mfGateway->PaymentMethodCode] = __($mfGateway->PaymentMethodEn);
            }
        }

        return $gateways;
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
}
