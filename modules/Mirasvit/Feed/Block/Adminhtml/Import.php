<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-feed
 * @version   1.1.38
 * @copyright Copyright (C) 2022 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Feed\Block\Adminhtml;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;

class Import extends Container
{
    /**
     * Import constructor.
     * @param Context $context
     */
    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_blockGroup = 'Mirasvit_Feed';
        $this->_controller = 'adminhtml_import';

        $this->buttonList->remove('save');
        $this->buttonList->remove('reset');
    }
}
