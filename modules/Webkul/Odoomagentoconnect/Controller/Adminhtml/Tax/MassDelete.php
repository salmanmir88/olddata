<?php
/**
 * Webkul Odoomagentoconnect Tax MassDelete Controller
 *
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Tax;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Webkul Odoomagentoconnect Tax MassDelete Controller class
 */
class MassDelete extends \Magento\Backend\App\Action
{
    public function __construct(
        Context $context,
        Filter $filter,
        \Webkul\Odoomagentoconnect\Model\Tax $model
    ) {
        $this->_model = $model;
        $this->_filter = $filter;
        parent::__construct($context);
    }
    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        try {
            $collection = $this->_model->getCollection();

            $selected = (array)$this->getRequest()->getParam('selected', []);
            $filters = (array)$this->getRequest()->getParam('filters', []);

            if (is_array($filters) && !empty($filters)) {
                foreach ($filters as $field => $value) {
                    if ($field == 'placeholder') {
                        continue;
                    }
                    $collection->addFieldToFilter($field, ['like'=>"%$value%"]);
                }
            }

            if ($selected) {
                $collection = $collection->addFieldToFilter('entity_id', ['in', $selected]);
            }

            if ($collection->getSize() > 0) {
                $collection->walk('delete');
                $this->messageManager->addSuccess(__('Tax deleted succesfully.'));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
