<?php

namespace Evince\ViewAll\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    public function getConfigValues($config_path) {
        return $this->scopeConfig->getValue(
                        $config_path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

}
