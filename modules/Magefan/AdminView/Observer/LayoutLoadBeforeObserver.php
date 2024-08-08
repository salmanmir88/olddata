<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AdminView\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magefan\AdminView\Model\Config;

/**
 * Remove blocks from admin footer
 */
class LayoutLoadBeforeObserver implements ObserverInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * LayoutLoadBeforeObserver constructor.
     * @param Config $config
     */
    public function __construct(
        Config $config
    ) {
        $this->config = $config;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($this->config->isEnabled()) {
            $layout = $observer->getLayout();
            foreach ($this->config->getAdminFooterConfig() as $k => $v) {
                if ($k == 'copyright' && !$v) {
                    $layout->getUpdate()->addHandle('admin_view_remove_footer_copyright');
                } elseif ($k == 'version' && !$v) {
                    $layout->getUpdate()->addHandle('admin_view_remove_footer_version');
                } elseif ($k == 'buttons' && !$v) {
                    $layout->getUpdate()->addHandle('admin_view_remove_footer_buttons');
                }
            }
        }
    }
}
