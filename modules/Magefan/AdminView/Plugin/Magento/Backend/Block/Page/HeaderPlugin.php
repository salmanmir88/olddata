<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AdminView\Plugin\Magento\Backend\Block\Page;

use Magento\Backend\Block\Page\Header;
use Magefan\AdminView\Model\Config;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

class HeaderPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * HeaderPlugin constructor.
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $config,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Header $subject
     * @param $result
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetViewFileUrl(Header $subject, $result)
    {
        if (false !== strpos($result, 'magento-logo.svg')) {
            if (!$this->config->isEnabled()) {
                return $result;
            }

            $mainLogo = $this->config->getMainLogo();
            if (!$mainLogo) {
                return $result;
            }

            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

            return $mediaUrl . 'default/' . $mainLogo;
        }

        if (false !== strpos($result, 'magento-icon.svg')) {
            if (!$this->config->isEnabled()) {
                return $result;
            }

            $mainLogo = $this->config->getMenuLogo();
            if (!$mainLogo) {
                return $result;
            }

            $mediaUrl = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

            return $mediaUrl . 'default/' . $mainLogo;
        }

        return $result;
    }
}
