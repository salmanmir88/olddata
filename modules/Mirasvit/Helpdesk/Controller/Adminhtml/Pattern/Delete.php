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
 * @package   mirasvit/module-helpdesk
 * @version   1.2.14
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Helpdesk\Controller\Adminhtml\Pattern;

use Magento\Framework\Controller\ResultFactory;

class Delete extends \Mirasvit\Helpdesk\Controller\Adminhtml\Pattern
{
    /**
     *
     */
    public function execute()
    {
        //        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        //        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        //
        if ($this->getRequest()->getParam('id') > 0) {
            try {
                $pattern = $this->patternFactory->create();

                $pattern->setId($this->getRequest()
                    ->getParam('id'))
                    ->delete();

                $this->messageManager->addSuccess(
                    __('Pattern was successfully deleted')
                );
                $this->_redirect('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_redirect('*/*/edit', ['id' => $this->getRequest()
                    ->getParam('id'), ]);
            }
        }
        $this->_redirect('*/*/');
    }
}
