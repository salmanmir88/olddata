<?php
namespace Vnecoms\Sms\Controller\Adminhtml\Blocklist;

use Vnecoms\Sms\Controller\Adminhtml\Blocklist\Action;

class Index extends Action
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_addBreadcrumb(__('Sms Nofitication'), __('Sms Nofitication'))->_addBreadcrumb(__('Block List'), __('Block List'));
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Block List'));
        $this->_view->renderLayout();
    }
}
