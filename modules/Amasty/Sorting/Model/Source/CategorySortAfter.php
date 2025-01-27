<?php

namespace Amasty\Sorting\Model\Source;

use Magento\Catalog\Model\Config\Source\ListSort;

class CategorySortAfter extends ListSort
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        array_unshift($options, [
            'value' => '',
            'label' => __('--Please Select--')
        ]);

        return $options;
    }
}
