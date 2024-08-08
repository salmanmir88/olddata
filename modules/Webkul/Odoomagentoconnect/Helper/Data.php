<?php
/**
 * Webkul Odoomagentoconnect Data Helper
 *
 * @category  Webkul
 * @package   Webkul_Odoomagentoconnect
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Odoomagentoconnect\Helper;

/**
 * Class Webkul Odoomagentoconnect Data Helper
 */
class Data extends \Magento\Search\Helper\Data
{
    public function __construct(
        \Magento\Backend\Model\Session $session
    ) {
    
        $this->_session = $session;
    }

    public function setToSession($odooCateg, $odooLang, $odooWarehouse, $odooInstance)
    {
        $this->_session->setOdooCateg($odooCateg);
        $this->_session->setOdooLang($odooLang);
        $this->_session->setOdooWarehouse($odooWarehouse);
        $this->_session->setOdooInstance($odooInstance);
    }
}
