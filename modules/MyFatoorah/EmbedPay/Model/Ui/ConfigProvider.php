<?php

namespace MyFatoorah\EmbedPay\Model\Ui;

use MyFatoorah\EmbedPay\Gateway\Config\Config;
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
            $data = (object) ['CountryCode' => '', 'SessionId' => ''];

            $error = $e->getMessage();
        }

        $config = [
            'payment' => [
                Config::CODE => [
                    'title'       => $this->_gatewayConfig->getTitle(),
                    'description' => $this->_gatewayConfig->getDescription(),
                    'countryCode' => $data->CountryCode,
                    'sessionId'   => $data->SessionId,
                    'mfError'     => $error
                ]
            ]
        ];

        return $config;
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
    public function listPaymentGateways() {

        return $this->_gatewayConfig->getMyfatoorahObject()->getEmbeddedSession();
    }

//---------------------------------------------------------------------------------------------------------------------------------------------------
}
