<?php

namespace Amasty\Reports\Model\OptionSource\Rule;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Status
 * @package Amasty\Reports\Model\OptionSource\Rule
 */
class Status implements ArrayInterface
{
    const INDEXED = 1;
    const PROCESSING = 0;

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::INDEXED,
                'label' => __('Indexed')
            ],
            [
                'value' => self::PROCESSING,
                'label' => __('Processing')
            ]
        ];
    }
}
