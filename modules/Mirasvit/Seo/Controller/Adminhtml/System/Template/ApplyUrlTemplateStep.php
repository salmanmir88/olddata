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
 * @package   mirasvit/module-seo
 * @version   2.1.11
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Seo\Controller\Adminhtml\System\Template;

use Magento\Framework\Controller\ResultFactory;

class ApplyUrlTemplateStep extends \Mirasvit\Seo\Controller\Adminhtml\System\Template
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        if ($step = $this->getRequest()->getParam('step')) {
            $worker = $this->systemTemplateWorker;
            $worker->setStep($step);
            if ($worker->run()) {
                ;

                return $resultPage;
            }
        }
    }
}
