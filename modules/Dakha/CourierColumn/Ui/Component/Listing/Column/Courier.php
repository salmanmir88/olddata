<?php

namespace Dakha\CourierColumn\Ui\Component\Listing\Column;

class Courier implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'saee', 'label' => __('Saee')],
            ['value' => 'aramex', 'label' => __('Aramex')],
            ['value' => 'logistiq', 'label' => __('logistiq')]
        ];
    }
}