<?php
/**
 * Copyright © FilterByBrand All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Dakha\FilterByBrand\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Eav\Model\Config;

class Data extends AbstractHelper
{

    /**
     * @var Config
     */
    protected $_eavConfig;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Config $eavConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Config $eavConfig
    ) {
        $this->_eavConfig = $eavConfig;
        parent::__construct($context);
    }

   /**
    * @param $label
    * @return
    */
   public function getOptionId($label)
   {
        $attribute = $this->_eavConfig->getAttribute('catalog_product', 'manufacturer');
        $options   = $attribute->getSource()->getAllOptions();
        $optionsId = '';
        
        foreach ($options as $option) {
            if ($label==$option['label']) {
                $optionsId = $option['value'];
            }
        }
        return $optionsId;
   }
}