<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Affiliate for Magento 2
*/

namespace Amasty\Affiliate\Block\SocialButtons;

class Product extends \Amasty\Affiliate\Block\SocialButtons\AbstractButtons
{
    /**
     * @var string
     */
    protected $_template = 'social_buttons/buttons.phtml';


    public function showConfig()
    {
        return $this->_scopeConfig->getValue('amasty_affiliate/friends/on_product_details');
    }
}
