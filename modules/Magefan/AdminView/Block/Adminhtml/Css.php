<?php
/**
 * Copyright © Magefan (support@magefan.com). All rights reserved.
 * Please visit Magefan.com for license details (https://magefan.com/end-user-license-agreement).
 */

namespace Magefan\AdminView\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magefan\AdminView\Model\Config;

class Css extends Template
{
    /**
     * @var Config
     */
    private $config;

    /**
     * Css constructor.
     * @param Template\Context $context
     * @param Config $config
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        array $data = []
    ) {
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * @param $field
     * @return string
     */
    public function getColor($field)
    {
        return $this->config->getColorSchema($field);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->config->isEnabled()) {
            return '';
        }
        return parent::_toHtml();
    }
}
