<?php
/**
 * Fetchr
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * https://fetchr.zendesk.com/hc/en-us/categories/200522821-Downloads
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to ws@fetchr.us so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Fetchr Magento Extension to newer
 * versions in the future. If you wish to customize Fetchr Magento Extension (Fetchr Shipping) for your
 * needs please refer to http://www.fetchr.us for more information.
 *
 * @author     Danish Kamal, Feiran Wang
 * @package    Fetchr Shipping
 * Used in creating options for fulfilment|delivery config value selection
 * @copyright  Copyright (c) 2018 Fetchr (http://www.fetchr.us)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Fetchr_Shipping_Helper_Data extends Magento\Framework\App\Helper\AbstractHelper
{
    protected $_scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig) {
        $this->_scopeConfig = $scopeConfig;
        $this->init();
    }

    protected $accountType;
    protected $serviceType;
    protected $address_id;
    protected $token;
    protected $userId;
    protected $connections = array();
        public function init() {
        $this->accountType = $this->_scopeConfig->getValue('carriers/fetchr/accounttype');
        $this->serviceType = $this->_scopeConfig->getValue('carriers/fetchr/servicetype');
        $this->address_id = $this->_scopeConfig->getValue('carriers/fetchr/addressid');
        $this->token = $this->_scopeConfig->getValue('carriers/fetchr/token');
    }
}
