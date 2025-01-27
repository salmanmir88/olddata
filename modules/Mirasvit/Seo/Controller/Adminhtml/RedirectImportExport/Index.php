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



namespace Mirasvit\Seo\Controller\Adminhtml\RedirectImportExport;

use Magento\Framework\Controller\ResultFactory;

class Index extends \Mirasvit\Seo\Controller\Adminhtml\RedirectImportExport
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $this->_initAction();
        $this->_addContent($resultPage->getLayout()
            ->createBlock('\Mirasvit\Seo\Block\Adminhtml\RedirectImportExport\ImportExport'));
        $resultPage->getConfig()->getTitle()->prepend(__('Import/Export Redirects'));

        return $resultPage;
    }
}
