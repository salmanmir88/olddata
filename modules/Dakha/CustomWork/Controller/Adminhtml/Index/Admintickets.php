<?php

namespace Dakha\CustomWork\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;

class Admintickets extends \Mirasvit\Helpdesk\Controller\Adminhtml\Ticket
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $resultPage->getConfig()->getTitle()->prepend(__('Customer Tickets'));


        $this->saveStoreSelection();
        $this->_initAction();

        return $resultPage;
    }
}
