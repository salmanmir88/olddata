<?php

namespace Meetanshi\GoogleSitemap\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Includeexclude
 * @package Meetanshi\GoogleSitemap\Model\Config\Source
 */
class Includeexclude implements ArrayInterface
{
    /**
     *
     */
    const INCLUDE_VALUE = 1;

    /**
     *
     */
    const EXCLUDE_VALUE = 0;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::INCLUDE_VALUE, 'label' => __('Include')],
            ['value' => self::EXCLUDE_VALUE, 'label' => __('Exclude')],
        ];
    }
}
