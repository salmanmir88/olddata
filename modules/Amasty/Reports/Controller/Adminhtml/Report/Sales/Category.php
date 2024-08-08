<?php

namespace Amasty\Reports\Controller\Adminhtml\Report\Sales;

/**
 * Class Category
 * @package Amasty\Reports\Controller\Adminhtml\Report\Sales
 */
class Category extends Sales
{
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Reports::reports_sales_category');
    }
}
