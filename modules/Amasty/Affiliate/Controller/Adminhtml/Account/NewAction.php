<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Affiliate for Magento 2
*/

namespace Amasty\Affiliate\Controller\Adminhtml\Account;

class NewAction extends \Amasty\Affiliate\Controller\Adminhtml\Account
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
