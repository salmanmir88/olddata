<?php

namespace Dakha\InvoiceStatusColumn\Ui\Component\Listing\Column;

class InvoiceStatus implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 1, 'label' => __('Yes')],
            ['value' => 2, 'label' => __('No')]
        ];
    }
}