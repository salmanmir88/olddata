<?php

namespace Amasty\Reports\Controller\Adminhtml\Rule;

use Amasty\Reports\Controller\Adminhtml\Rule as RuleController;
use Magento\Backend\App\Action;

/**
 * Class NewAction
 * @package Amasty\Reports\Controller\Adminhtml\Rule
 */
class NewAction extends RuleController
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
