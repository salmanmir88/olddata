<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Affiliate for Magento 2
*/

namespace Amasty\Affiliate\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class Status implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            '0' => __('Disabled'),
            '1' => __('Enabled'),
        ];
    }
}
