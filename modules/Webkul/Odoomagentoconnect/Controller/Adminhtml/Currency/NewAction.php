<?php
/**
 * Webkul Odoomagentoconnect Currency NewAction Controller
 *
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Currency;

/**
 * Webkul Odoomagentoconnect Currency NewAction Controller class
 */
class NewAction extends \Webkul\Odoomagentoconnect\Controller\Adminhtml\Currency
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::currency_new');
    }
}
