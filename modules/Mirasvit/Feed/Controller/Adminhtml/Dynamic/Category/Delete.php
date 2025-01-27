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


namespace Mirasvit\Feed\Controller\Adminhtml\Dynamic\Category;

use Mirasvit\Feed\Controller\Adminhtml\Dynamic\Category;

class Delete extends Category
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $model = $this->initModel();
            $model->delete();

            $this->messageManager->addSuccess(__('Item was successfully deleted'));
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
