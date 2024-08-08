<?php
/**
 * Webkul Odoomagentoconnect Carrier NewAction Controller
 *
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Controller\Adminhtml\Carrier;

/**
 * Webkul Odoomagentoconnect Carrier NewAction Controller class
 */
class NewAction extends \Webkul\Odoomagentoconnect\Controller\Adminhtml\Carrier
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
        return $this->_authorization->isAllowed('Webkul_Odoomagentoconnect::carrier_new');
    }
}
