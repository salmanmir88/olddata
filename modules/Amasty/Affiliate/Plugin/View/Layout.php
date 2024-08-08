<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Affiliate for Magento 2
*/

namespace Amasty\Affiliate\Plugin\View;

class Layout
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Layout constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
    }

    public function afterIsCacheable(
        \Magento\Framework\View\Layout $subject,
        $result
    ) {
        $affiliateUlrParameter = $this->scopeConfig->getValue('amasty_affiliate/url/parameter');
        $params = $this->request->getParams();
        if (key_exists($affiliateUlrParameter, $params)
            && key_exists('referring_service', $params)
        ) {
            $result = false;
        }

        return $result;
    }
}
