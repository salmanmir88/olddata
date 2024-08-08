<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Affiliate for Magento 2
*/

namespace Amasty\Affiliate\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class TermsConditions implements ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => 'Not Accepted'],
            ['value' => 1, 'label' => 'Accepted']
        ];
    }
}
