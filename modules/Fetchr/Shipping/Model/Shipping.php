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

class Fetchr_Shipping_Model_Shipping extends \Magento\Shipping\Model\Shipping
{

    protected $_scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig) {
        $this->_scopeConfig = $scopeConfig;
    }

    public function collectCarrierRates($carrierCode, $request)
    {
        if (!$this->_checkCarrierAvailability($carrierCode, $request)) {
            return $this;
        }
        return parent::collectCarrierRates($carrierCode, $request);
    }

    protected function _checkCarrierAvailability($carrierCode, $request = null)
    {
        $showInFronend  = $this->_scopeConfig->getValue('carriers/fetchr/showinfrontend');
        if(!$showInFronend){
            if($carrierCode == 'fetchr'){ #Hide Flat Rate for non logged in customers
                return false;
            }
        }
        return true;
    }
}
